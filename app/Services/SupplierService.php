<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Supplier
    {
        $supplier = Supplier::query()->create($validated);
        $supplier->manufacturers()->sync($validated['manufacturers']);
        return $supplier;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Supplier $supplier, array $validated): Supplier
    {
        $supplier->update($validated);
        $supplier->manufacturers()->sync($validated['manufacturers']);
        return $supplier->refresh();
    }

    /**
     * @param Supplier $supplier
     * @return void
     */
    public function destroy(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
