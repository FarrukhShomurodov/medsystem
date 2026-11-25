<?php

namespace App\Services\Stock;

use App\Enums\StockMovementReason;
use App\Exceptions\Stock\InsufficientStockException;
use App\Exceptions\Stock\StockException;
use App\Models\Admin;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use LogicException;

/**
 * The only code that changes stock quantities, and the rules it keeps:
 *
 * - A row holds one item in one place. Main warehouse medications are batches, one row per receipt
 *   line (main_warehouse_medications); department warehouses keep the batches they received
 *   (warehouse_accountings, with batch number and expiry); supplies, household materials and
 *   pharmacy stock have one row per item. `quantity` is the amount on hand. A unique key on each of
 *   these tables (migration add_unique_keys_to_stock_rows) keeps it, and lot() relies on it.
 * - Units are taken earliest expiry first (FEFO) across as many rows as needed, or not at all:
 *   a shortage throws, and the caller's transaction rolls the whole document back. Expired
 *   batches are never taken (callers pass unexpired() batches): they wait to be written off.
 * - Rows are locked (SELECT ... FOR UPDATE) before they are read for a change, so two requests
 *   can never sell the same units. Callers run inside DB::transaction().
 * - Every change is recorded in stock_movements with its reason, document and admin.
 */
final class StockLedger
{
    /**
     * The largest quantity one line of a stock document (receipt, order, transfer) may carry: far
     * above any real line, and it keeps the integer quantity columns from overflowing.
     */
    public const int MAX_LINE_QUANTITY = 1_000_000;

    /**
     * Takes units out of the rows of one item in one place, in the order the query gives
     * (earliest expiry first for batches).
     *
     * @template TLot of Model&StockLot
     *
     * @param  Builder<TLot>  $lots
     * @return list<array{lot: TLot, quantity: int}> what was taken from which row
     *
     * @throws InsufficientStockException
     */
    public function take(Builder $lots, int $quantity, StockMovementReason $reason, ?Model $source, string $itemName, string $place): array
    {
        $rows = $lots->where('quantity', '>', 0)->lockForUpdate()->get();
        $available = (int) $rows->sum('quantity');

        if ($available < $quantity) {
            throw new InsufficientStockException($itemName, $quantity, $available, $place);
        }

        $taken = [];
        $remaining = $quantity;

        foreach ($rows as $lot) {
            if ($remaining === 0) {
                break;
            }

            $part = min($remaining, (int) $lot->getAttribute('quantity'));
            $this->change($lot, -$part, $reason, $source);
            $taken[] = ['lot' => $lot, 'quantity' => $part];
            $remaining -= $part;
        }

        return $taken;
    }

    /**
     * The locked row of an item in a place, to put units into; an empty one is created when the
     * place never held the item.
     *
     * SELECT ... FOR UPDATE locks nothing while the row does not exist, so the empty row is
     * inserted first (ON CONFLICT DO NOTHING on the table's unique key) and then read locked. A
     * concurrent first put of the same item waits on that insert until this transaction ends,
     * then reads the committed row: the place keeps one row. The attributes must be that key.
     *
     * @template TLot of Model&StockLot
     *
     * @param  Builder<TLot>  $lots
     * @param  array<string, mixed>  $attributes  the columns of the row's unique key
     * @return TLot
     */
    public function lot(Builder $lots, array $attributes): Model
    {
        $lot = $this->lockedRow($lots, $attributes);

        if ($lot !== null) {
            return $lot;
        }

        $model = $lots->getModel();
        $timestamps = $model->usesTimestamps()
            ? [$model->getCreatedAtColumn() => $model->freshTimestamp(), $model->getUpdatedAtColumn() => $model->freshTimestamp()]
            : [];

        $model->newQueryWithoutScopes()->insertOrIgnore([...$attributes, 'quantity' => 0, ...$timestamps]);

        return $this->lockedRow($lots, $attributes)
            ?? throw new LogicException('The stock row inserted into '.$model->getTable().' cannot be read back: the attributes are not its key.');
    }

    /** Puts units into a row locked by lot(), or into a new batch. */
    public function put(Model&StockLot $lot, int $quantity, StockMovementReason $reason, ?Model $source): void
    {
        $this->change($lot, $quantity, $reason, $source);
    }

    /** Removes a whole row (a written-off batch); the ledger keeps what it held. */
    public function writeOff(Model&StockLot $lot): void
    {
        $this->record($lot, -(int) $lot->getAttribute('quantity'), StockMovementReason::WriteOff, null);
        $lot->delete();
    }

    /**
     * Puts back what a document took out, into the very rows it came from (a returned prescription).
     *
     * @throws StockException when a row was written off in the meantime
     */
    public function restore(Model $source, StockMovementReason $takenFor, StockMovementReason $reason): void
    {
        $movements = StockMovement::query()
            ->whereMorphedTo('source', $source)
            ->where('reason', $takenFor)
            ->get()
            ->groupBy(fn (StockMovement $movement): string => $movement->stock_type.'#'.$movement->stock_id);

        foreach ($movements as $group) {
            $first = $group->firstOrFail();
            $class = Relation::getMorphedModel($first->stock_type);

            if ($class === null || ! is_subclass_of($class, Model::class) || ! is_subclass_of($class, StockLot::class)) {
                throw new LogicException("Stock row type {$first->stock_type} is not a stock row of the morph map.");
            }

            $lot = $class::query()->whereKey($first->stock_id)->lockForUpdate()->first()
                ?? throw new StockException('Партия, из которой выдан препарат, уже списана: вернуть его на склад нельзя.');

            $this->change($lot, -$group->sum(fn (StockMovement $movement): int => $movement->quantity), $reason, $source);
        }
    }

    /**
     * @template TLot of Model&StockLot
     *
     * @param  Builder<TLot>  $lots
     * @param  array<string, mixed>  $attributes
     * @return TLot|null
     *
     * @phpstan-impure It reads the table, which lot() writes to between two calls.
     */
    private function lockedRow(Builder $lots, array $attributes): ?Model
    {
        return (clone $lots)->where($attributes)->lockForUpdate()->first();
    }

    private function change(Model&StockLot $lot, int $quantity, StockMovementReason $reason, ?Model $source): void
    {
        $lot->setAttribute('quantity', (int) $lot->getAttribute('quantity') + $quantity);
        $lot->save();

        $this->record($lot, $quantity, $reason, $source);
    }

    private function record(Model&StockLot $lot, int $quantity, StockMovementReason $reason, ?Model $source): void
    {
        $item = $lot->stockItem();

        StockMovement::query()->create([
            'stock_type' => $lot->getMorphClass(),
            'stock_id' => $lot->getKey(),
            'item_type' => $item->getRelated()->getMorphClass(),
            'item_id' => $lot->getAttribute($item->getForeignKeyName()),
            'quantity' => $quantity,
            'reason' => $reason,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'admin_id' => Auth::guard(Admin::GUARD)->id(),
        ]);
    }
}
