<?php

namespace App\Services;

use App\Models\Allergy;

class AllergyService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Allergy
    {
        return Allergy::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Allergy $allergy, array $validated): Allergy
    {
        $allergy->update($validated);

        return $allergy->refresh();
    }

    public function destroy(Allergy $allergy): void
    {
        $allergy->delete();
    }
}
