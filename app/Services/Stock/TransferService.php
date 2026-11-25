<?php

namespace App\Services\Stock;

use App\Enums\StockMovementReason;
use App\Enums\TransferStatus;
use App\Exceptions\Stock\InvalidStatusTransitionException;
use App\Exceptions\Stock\StockException;
use App\Models\PharmacyMedication;
use App\Models\Transfer;
use App\Models\WarehouseAccounting;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Transfers of medications from a department warehouse to a pharmacy (stock rules: see StockLedger).
 * A transfer is a draft while pending; completing it moves the stock, exactly once.
 */
class TransferService
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @param  array{from_warehouse_id: int, to_pharmacy_id: int, notes?: ?string, medications: list<array{medication_id: int, quantity: int}>}  $validated
     */
    public function store(array $validated): Transfer
    {
        return DB::transaction(function () use ($validated): Transfer {
            $transfer = Transfer::query()->create([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_pharmacy_id' => $validated['to_pharmacy_id'],
                'transfer_date' => today(),
                'status' => TransferStatus::Pending,
                'notes' => $validated['notes'] ?? null,
            ]);

            $transfer->medications()->createMany($validated['medications']);

            return $transfer;
        });
    }

    /**
     * @param  array{from_warehouse_id: int, to_pharmacy_id: int, notes?: ?string, medications: list<array{medication_id: int, quantity: int}>}  $validated
     */
    public function update(Transfer $transfer, array $validated): void
    {
        DB::transaction(function () use ($transfer, $validated): void {
            $transfer = $this->lockEditable($transfer);

            $transfer->update([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_pharmacy_id' => $validated['to_pharmacy_id'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $transfer->medications()->delete();
            $transfer->medications()->createMany($validated['medications']);
        });
    }

    public function destroy(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer): void {
            $this->lockEditable($transfer)->delete();
        });
    }

    /**
     * Moves a transfer along its lifecycle. Completing it takes every line from the department
     * warehouse (earliest expiry first) into the pharmacy; a shortage refuses the whole transfer.
     */
    public function changeStatus(Transfer $transfer, TransferStatus $status): void
    {
        DB::transaction(function () use ($transfer, $status): void {
            $transfer = Transfer::query()->whereKey($transfer->getKey())->lockForUpdate()->firstOrFail();
            $current = $transfer->status;

            if (! $current->canTransitionTo($status)) {
                throw new InvalidStatusTransitionException('Перевод', $current->label(), $status->label());
            }

            if ($status === TransferStatus::Completed) {
                $this->moveStock($transfer);
            }

            $transfer->update(['status' => $status]);
        });
    }

    private function moveStock(Transfer $transfer): void
    {
        $transfer->load('medications.medication');
        $from = 'склад «'.$transfer->fromWarehouse()->firstOrFail()->name.'»';

        foreach ($transfer->medications->sortBy('medication_id') as $line) {
            $medication = $line->medication
                ?? throw new LogicException("Transfer line {$line->id} has no medication.");
            $lots = WarehouseAccounting::query()
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->where('medication_id', $line->medication_id)
                ->unexpired()
                ->orderBy('expiration_date')
                ->orderBy('id');

            $this->ledger->take(
                $lots,
                $line->quantity,
                StockMovementReason::Transfer,
                $transfer,
                $medication->name,
                $from,
            );

            $pharmacyRow = $this->ledger->lot(PharmacyMedication::query(), [
                'pharmacy_id' => $transfer->to_pharmacy_id,
                'medication_id' => $line->medication_id,
            ]);
            $this->ledger->put($pharmacyRow, $line->quantity, StockMovementReason::Transfer, $transfer);
        }
    }

    private function lockEditable(Transfer $transfer): Transfer
    {
        $transfer = Transfer::query()->whereKey($transfer->getKey())->lockForUpdate()->firstOrFail();

        if (! $transfer->status->isEditable()) {
            throw new StockException('Изменить или удалить можно только перевод в ожидании: по этому переводу уже принято решение.');
        }

        return $transfer;
    }
}
