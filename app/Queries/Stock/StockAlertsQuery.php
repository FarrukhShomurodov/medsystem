<?php

namespace App\Queries\Stock;

use App\Enums\SupplyRequestStatus;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\SupplyRequest;
use App\Models\WarehouseAccounting;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * What the stock needs someone to act on, counted by the database (one query per figure): supply
 * requests to decide, medication batches near or past their expiry, items the main warehouse runs
 * low on or has run out of.
 *
 * A medication's stock in a place is the units of its batches in date there (onHand()): an expired
 * batch is never given out, so it counts for nothing until it is written off. The thresholds are
 * the medication's own (Medication::isLowStock(), expiryWarningDue()).
 */
final class StockAlertsQuery
{
    /** Supply requests of the department warehouses the main warehouse has not decided yet. */
    public function pendingSupplyRequests(): int
    {
        return SupplyRequest::query()->where('status', SupplyRequestStatus::Pending)->count();
    }

    /** Medication batches of the main warehouse near or past their expiry date. */
    public function expiryInMainWarehouse(CarbonInterface $today): ExpiryWarnings
    {
        return $this->expiry(
            MainWarehouseMedication::query()->where('main_warehouse_medications.main_warehouse_id', MainWarehouse::MAIN),
            'main_warehouse_medications',
            $today,
        );
    }

    /** Medication batches of the department warehouses near or past their expiry date. */
    public function expiryInWardWarehouses(CarbonInterface $today): ExpiryWarnings
    {
        return $this->expiry(WarehouseAccounting::query(), 'warehouse_accountings', $today);
    }

    /**
     * Items of the main warehouse with nothing left: medications with no units in date (their
     * batches used up or expired), supplies and household materials whose row stands at zero.
     * Written-off rows are gone, so an item only counts while the warehouse still keeps a row of
     * it. These are the items the receipts page lists as running out with 0.
     */
    public function outOfStockInMainWarehouse(CarbonInterface $today): int
    {
        $medications = DB::query()
            ->fromSub($this->onHand($this->mainWarehouseBatches(), 'main_warehouse_medications', $today), 'stock')
            ->where('on_hand', 0)
            ->selectRaw('count(*)');

        $row = DB::query()
            ->selectSub($medications, 'medications')
            ->selectSub($this->emptyRows(MainWarehouseMedicalSupply::query()), 'supplies')
            ->selectSub($this->emptyRows(MainWarehouseHouseholdMaterial::query()), 'household')
            ->first();

        return $row === null ? 0 : (int) $row->medications + (int) $row->supplies + (int) $row->household;
    }

    /**
     * The medications the main warehouse runs low on (Medication::isLowStock()), by name, with
     * their units in date: the "Заканчивающиеся позиции" of the receipts page. A drug is listed
     * while the warehouse keeps a batch of it, so one with nothing left in date is listed with 0.
     *
     * @return list<array{medication: Medication, onHand: int}>
     */
    public function runningLowInMainWarehouse(CarbonInterface $today): array
    {
        $onHand = $this->medicationsOnHandInMainWarehouse($today);
        $lines = [];

        $medications = Medication::query()
            ->whereIn('id', $this->mainWarehouseBatches()->select('medication_id'))
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'notify_min_qty']);

        foreach ($medications as $medication) {
            $units = $onHand[$medication->id] ?? 0;

            if ($medication->isLowStock($units)) {
                $lines[] = ['medication' => $medication, 'onHand' => $units];
            }
        }

        return $lines;
    }

    /**
     * The units in date of each medication the main warehouse keeps a batch of.
     *
     * @return array<int, int> medication id => units
     */
    public function medicationsOnHandInMainWarehouse(CarbonInterface $today): array
    {
        return $this->onHand($this->mainWarehouseBatches(), 'main_warehouse_medications', $today)
            ->get()
            ->mapWithKeys(fn (stdClass $row): array => [(int) $row->medication_id => (int) $row->on_hand])
            ->all();
    }

    /**
     * The units in date of each medication in each department warehouse (only in $warehouseId
     * when given).
     *
     * @return array<int, array<int, int>> warehouse id => medication id => units
     */
    public function medicationsOnHandInWardWarehouses(CarbonInterface $today, ?int $warehouseId = null): array
    {
        $batches = WarehouseAccounting::query()
            ->when($warehouseId, fn (Builder $query, int $id) => $query->where('warehouse_accountings.warehouse_id', $id));
        $rows = $this->onHand($batches, 'warehouse_accountings', $today)
            ->addSelect('warehouse_accountings.warehouse_id')
            ->groupBy('warehouse_accountings.warehouse_id')
            ->get();

        $units = [];

        foreach ($rows as $row) {
            $units[(int) $row->warehouse_id][(int) $row->medication_id] = (int) $row->on_hand;
        }

        return $units;
    }

    /**
     * Batches with units left whose expiry warning is due: from the medication's own
     * notify_before_days before the expiry date on, expired batches included. These are the rows
     * the remains pages mark "истекает", or "просрочен" when expired (Medication::expiryWarningDue()
     * and the batches' isExpired(), the same rules in PHP).
     *
     * @template TBatch of Model
     *
     * @param  Builder<TBatch>  $batches
     * @param  literal-string  $table  the table of the batches
     */
    private function expiry(Builder $batches, string $table, CarbonInterface $today): ExpiryWarnings
    {
        $row = $batches
            ->join('medications', 'medications.id', '=', "{$table}.medication_id")
            ->where("{$table}.quantity", '>', 0)
            ->whereRaw("{$table}.expiration_date <= cast(? as date) + medications.notify_before_days", [$today->toDateString()])
            ->toBase()
            ->selectRaw("count(*) as due, count(*) filter (where {$table}.expiration_date < cast(? as date)) as expired", [$today->toDateString()])
            ->first();

        return new ExpiryWarnings((int) ($row->due ?? 0), (int) ($row->expired ?? 0));
    }

    /**
     * medication_id and on_hand, the units of its batches in date, per medication of the batches.
     *
     * @template TBatch of Model
     *
     * @param  Builder<TBatch>  $batches
     * @param  literal-string  $table  the table of the batches
     */
    private function onHand(Builder $batches, string $table, CarbonInterface $today): QueryBuilder
    {
        return $batches->toBase()
            ->select("{$table}.medication_id")
            ->selectRaw("coalesce(sum({$table}.quantity) filter (where {$table}.expiration_date >= cast(? as date)), 0) as on_hand", [$today->toDateString()])
            ->groupBy("{$table}.medication_id");
    }

    /**
     * @return Builder<MainWarehouseMedication>
     */
    private function mainWarehouseBatches(): Builder
    {
        return MainWarehouseMedication::query()->where('main_warehouse_medications.main_warehouse_id', MainWarehouse::MAIN);
    }

    /**
     * @template TRow of Model
     *
     * @param  Builder<TRow>  $rows
     * @return Builder<TRow>
     */
    private function emptyRows(Builder $rows): Builder
    {
        return $rows->where('main_warehouse_id', MainWarehouse::MAIN)->where('quantity', 0)->selectRaw('count(*)');
    }
}
