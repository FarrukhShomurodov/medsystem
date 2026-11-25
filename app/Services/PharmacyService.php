<?php

namespace App\Services;

use App\Models\Pharmacy;
use Illuminate\Support\Facades\DB;

class PharmacyService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Pharmacy
    {
        return DB::transaction(function () use ($validated): Pharmacy {
            $pharmacy = Pharmacy::query()->create($validated);
            $validated['pharmacy_id'] = $pharmacy->id;

            $pharmacy->license()->create($validated);
            $pharmacy->support()->create($validated);

            return $pharmacy;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Pharmacy $pharmacy, array $validated): Pharmacy
    {
        return DB::transaction(function () use ($validated, $pharmacy): Pharmacy {
            $pharmacy->update(array_intersect_key($validated, array_flip($pharmacy->getFillable())));
            $validated['pharmacy_id'] = $pharmacy->id;

            $pharmacy->license()->updateOrCreate(['pharmacy_id' => $pharmacy->id], array_intersect_key($validated, array_flip($pharmacy->license()->getRelated()->getFillable())));
            $pharmacy->support()->updateOrCreate(['pharmacy_id' => $pharmacy->id], array_intersect_key($validated, array_flip($pharmacy->support()->getRelated()->getFillable())));

            return $pharmacy;
        });
    }

    public function destroy(Pharmacy $pharmacy): void
    {
        $pharmacy->delete();
    }
}
