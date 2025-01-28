<?php

namespace App\Services;

use App\Models\ChronicDisease;

class ChronicDiseaseService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ChronicDisease
    {
        return ChronicDisease::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ChronicDisease $chronicDisease, array $validated): ChronicDisease
    {
        $chronicDisease->update($validated);
        return $chronicDisease->refresh();
    }

    /**
     * @param ChronicDisease
     * @return void
     */
    public function destroy(ChronicDisease $chronicDisease): void
    {
        $chronicDisease->delete();
    }
}
