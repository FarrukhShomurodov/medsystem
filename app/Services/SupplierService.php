<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

class SupplierService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Supplier
    {
        if (!empty($validated['contract_terms']) && $validated['contract_terms'] instanceof \Illuminate\Http\UploadedFile) {
            $validated['contract_terms'] = $validated['contract_terms']->store('supplier_contract_terms', 'public');
        }

        $supplier = Supplier::query()->create($validated);
        $supplier->manufacturers()->sync($validated['manufacturers']);
        return $supplier;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Supplier $supplier, array $validated): Supplier
    {
        if (!empty($validated['contract_terms']) && $validated['contract_terms'] instanceof \Illuminate\Http\UploadedFile) {
            if ($supplier->contract_terms) {
                Storage::disk('public')->delete($supplier->contract_terms);
            }

            $validated['contract_terms'] = $validated['contract_terms']->store('supplier_contract_terms', 'public');
        }

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
