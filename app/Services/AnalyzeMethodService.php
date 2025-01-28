<?php

namespace App\Services;

use App\Models\AnalyzeMethod;

class AnalyzeMethodService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): AnalyzeMethod
    {
        return AnalyzeMethod::query()->create($validated);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(AnalyzeMethod $method, array $validated): AnalyzeMethod
    {
        $method->update($validated);
        return $method->refresh();
    }

    /**
     * @param AnalyzeMethod $method
     * @return void
     */
    public function destroy(AnalyzeMethod $method): void
    {
        $method->delete();
    }
}
