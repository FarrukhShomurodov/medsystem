<?php

namespace App\Services;

use App\Models\AnalyzeType;

class AnalyzeTypeService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): AnalyzeType
    {
        return AnalyzeType::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(AnalyzeType $type, array $validated): AnalyzeType
    {
        $type->update($validated);
        return $type->refresh();
    }

    /**
     * @param AnalyzeType $type
     * @return void
     */
    public function destroy(AnalyzeType $type): void
    {
        $type->delete();
    }
}
