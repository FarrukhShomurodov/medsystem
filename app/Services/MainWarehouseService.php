<?php

namespace App\Services;

use App\Models\MainWarehouse;
use Illuminate\Support\Facades\DB;

class MainWarehouseService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function storeGoodsStore(array $validated): MainWarehouse {
        $warehouse = MainWarehouse::query()->first();

        return DB::transaction(function () use ($validated, $warehouse){
            $syncMedications = [];
            foreach ($validated['medications'] as $medication) {
                $medicationId = $medication['id'];

                $syncMedications[$medicationId] = [
                    'quantity' => $medication['quantity'],
                    'expiration_date' => $medication['expiration_date'],
                    'date_of_receipt' => $medication['date_of_receipt'],
                    'batch_number' => $medication['batch_number'],
                    'retail_price' => $medication['retail_price'],
                    'wholesale_price' => $medication['wholesale_price'],
                    'supplier_id' => $medication['supplier_id'],
                    'packaging_type' => $medication['packaging_type'],
                    'quantity_per_package' => $medication['quantity_per_package'],
                    'package_size' => $medication['package_size'],
                ];
            }

            $warehouse->medication()->sync($syncMedications);

            $warehouse->medicalSupplies()->sync($validated['supplies']);
            $warehouse->householdMaterial()->sync($validated['household']);

            return $warehouse;
        });
    }
}
