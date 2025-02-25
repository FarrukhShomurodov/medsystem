<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Warehouse
    {
        return DB::transaction(function () use ($validated) {
            $warehouse = Warehouse::query()->create($validated);
            $validated['warehouse_id'] = $warehouse->id;

            // Создание связанных записей
            $warehouse->contact()->create($validated);
            $warehouse->capacity()->create($validated);
//            $warehouse->inventory()->create($validated);
            $warehouse->storageHandling()->create($validated);
            $warehouse->transfer()->create($validated);

            foreach ($validated['medications'] as $index => $medication) {
                if (is_int($index)){
                    $medication['medication_id'] = $index;
                    $warehouse->accounting()->create($medication);
                }
            }

            // Синхронизация
//            if (!empty($validated['equipments'])) {
//                $warehouse->equipments()->sync($validated['equipments']);
//            }

            return $warehouse;
        }, 5);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Warehouse $warehouse, array $validated)
    {
        return DB::transaction(function () use ($validated, $warehouse) {
            $warehouse->update($validated);
            $validated['Warehouse_id'] = $warehouse->id;

            // Создание связанных записей
            $warehouse->contact()->create($validated);
            $warehouse->accounting()->create($validated);
            $warehouse->capacity()->create($validated);
            $warehouse->inventory()->create($validated);
            $warehouse->storageHandling()->create($validated);
            $warehouse->transfer()->create($validated);
        });
    }

    /**
     * @param Warehouse $warehouse
     * @return void
     */
    public function destroy(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }
}
