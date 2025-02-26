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
                if (is_int($index)) {
                    $medication['medication_id'] = $index;
                    $warehouse->accounting()->create($medication);
                }
            }

            return $warehouse;
        }, 5);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Warehouse $warehouse, array $validated)
    {
        return DB::transaction(function () use ($validated, $warehouse) {
            $warehouse->update(array_intersect_key($validated, array_flip($warehouse->getFillable())));
            $validated['warehouse_id'] = $warehouse->id;

            $warehouse->contact()->updateOrCreate(['warehouse_id' => $warehouse->id], array_intersect_key($validated, array_flip($warehouse->contact->getFillable())));
            $warehouse->capacity()->updateOrCreate(['warehouse_id' => $warehouse->id], array_intersect_key($validated, array_flip($warehouse->capacity->getFillable())));
            $warehouse->storageHandling()->updateOrCreate(['warehouse_id' => $warehouse->id], array_intersect_key($validated, array_flip($warehouse->storageHandling->getFillable())));
            $warehouse->transfer()->updateOrCreate(['warehouse_id' => $warehouse->id], array_intersect_key($validated, array_flip($warehouse->transfer->getFillable())));

            foreach ($validated['medications'] as $medicationId => $medication) {
                if (is_array($medication) && isset($medication['id'])) {
                    $warehouse->accounting()->updateOrCreate(
                        ['medication_id' => $medication['id']],
                        array_merge($medication, ['warehouse_id' => $warehouse->id])
                    );
                }
            }

            return $warehouse;
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
