<?php

namespace App\Services;

use App\Models\Pharmacy;
use App\Models\Transfer;
use App\Models\TransferMedication;
use App\Models\WarehouseAccounting;
use App\Models\PharmacyMedication;
use Exception;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function store(array $validated): Transfer
    {
        return DB::transaction(function () use ($validated) {
            $transfer = Transfer::create([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_pharmacy_id' => $validated['to_pharmacy_id'],
                'transfer_date' => now(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['medications'] as $medication) {
                TransferMedication::create([
                    'transfer_id' => $transfer->id,
                    'medication_id' => $medication['medication_id'],
                    'quantity' => $medication['quantity'],
                ]);
            }

            return $transfer;
        });
    }

    public function updating(Transfer $transfer): void
    {
        if ($transfer->status === 'completed') {
            DB::transaction(function () use ($transfer) {
                $this->processTransfer($transfer);
            });
        }
    }

    private function processTransfer(Transfer $transfer): void
    {
        foreach ($transfer->medications as $transferMed) {
            $available = $this->getAvailableQuantity($transfer->from_warehouse_id, $transferMed->medication_id);

            if ($available < $transferMed->quantity) {
                throw new Exception("Недостаточно препарата ID {$transferMed->medication_id} на складе.");
            }

            $this->decreaseWarehouseStock($transfer->from_warehouse_id, $transferMed->medication_id, $transferMed->quantity);
            $this->increasePharmacyStock($transfer->to_pharmacy_id, $transferMed->medication_id, $transferMed->quantity);
        }
    }

    private function getAvailableQuantity(int $warehouseId, int $medicationId): int
    {
        return WarehouseAccounting::where(['warehouse_id' => $warehouseId, 'medication_id' => $medicationId])
            ->value('quantity') ?? 0;
    }

    private function decreaseWarehouseStock(int $warehouseId, int $medicationId, int $quantity): void
    {
        WarehouseAccounting::where('warehouse_id', $warehouseId)
            ->where('medication_id', $medicationId)
            ->decrement('quantity', $quantity);

    }

    private function increasePharmacyStock(int $pharmacyId, int $medicationId, int $quantity): void
    {
        $pharmacy = Pharmacy::findOrFail($pharmacyId);

        // Получаем текущий остаток лекарства в аптеке
        $existing = DB::table('pharmacy_medications')
            ->where('pharmacy_id', $pharmacyId)
            ->where('medication_id', $medicationId)
            ->first();

        if ($existing) {
            // Если запись есть, обновляем количество
            DB::table('pharmacy_medications')
                ->where('pharmacy_id', $pharmacyId)
                ->where('medication_id', $medicationId)
                ->update([
                    'quantity' => $existing->quantity + $quantity,
                    'updated_at' => now(),
                ]);
        } else {
            // Если записи нет, создаем новую
            DB::table('pharmacy_medications')->insert([
                'pharmacy_id' => $pharmacyId,
                'medication_id' => $medicationId,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
