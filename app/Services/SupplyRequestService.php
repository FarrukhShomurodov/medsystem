<?php

namespace App\Services;

use App\Models\HouseholdMaterial;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\SupplyRequest;
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

        foreach ($validated['items'] as $item) {
            $requestModel->items()->create([
                'supplyable_type' => $this->resolveType($item['type']),
                'supplyable_id' => $item['id'],
                'requested_quantity' => $item['quantity'],
            ]);
        }

        return $requestModel;
    }

    private function resolveType(string $type): string
    {
        return match ($type) {
            'medication' => Medication::class,
            'medical_supply' => MedicalSupply::class,
            'household_material' => HouseholdMaterial::class,
            default => throw new Exception("Неверный тип: $type"),
        };
    }
}
