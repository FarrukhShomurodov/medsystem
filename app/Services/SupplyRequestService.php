<?php

namespace App\Services;

use App\Models\HouseholdMaterial;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\SupplyRequest;
use App\Models\Warehouse;
use Exception;

class SupplyRequestService
{
    public function store(array $validated)
    {
        $requestModel = SupplyRequest::create([
            'warehouse_id' => $validated['warehouse_id'],
            'main_warehouse_id' => 1,
            'status' => 'pending',
        ]);

        $warehouse = Warehouse::query()->find($validated['warehouse_id']);
        foreach ($validated['items'] as $item) {
            $requestModel->items()->create([
                'supplyable_type' => $this->resolveType($warehouse->warehouse_type_id),
                'supplyable_id' => $item['id'],
                'requested_quantity' => $item['quantity'],
            ]);
        }

        return $requestModel;
    }

    private function resolveType($type): string
    {
        return match ($type) {
            1 => Medication::class,
            3 => MedicalSupply::class,
            2 => HouseholdMaterial::class,
            default => throw new Exception("Неверный тип: $type"),
        };
    }
}
