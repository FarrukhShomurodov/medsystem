<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupplierService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Supplier
    {
        if (($validated['contract_terms'] ?? null) instanceof UploadedFile) {
            $validated['contract_terms'] = $validated['contract_terms']->store('supplier_contract_terms', 'public');
        }

        return DB::transaction(function () use ($validated): Supplier {
            $supplier = Supplier::query()->create($validated);
            $supplier->manufacturers()->sync($validated['manufacturers']);

            return $supplier;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Supplier $supplier, array $validated): Supplier
    {
        $replaced = null;

        if (($validated['contract_terms'] ?? null) instanceof UploadedFile) {
            $replaced = $supplier->contract_terms;
            $validated['contract_terms'] = $validated['contract_terms']->store('supplier_contract_terms', 'public');
        }

        return DB::transaction(function () use ($supplier, $validated, $replaced): Supplier {
            $supplier->update($validated);
            $supplier->manufacturers()->sync($validated['manufacturers']);

            // A failed update still points at the old contract, so it may go only after the commit.
            if ($replaced) {
                DB::afterCommit(fn () => Storage::disk('public')->delete($replaced));
            }

            return $supplier->refresh();
        });
    }

    public function destroy(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
