<?php

namespace App\Services;

use App\Models\Analyze;
use App\Models\AnalyzeResult;

class AnalyzeResultService
{
    /**
     * @param array $validated
     * @param Analyze $analyze
     * @return void
     */
    public function store(array $validated, Analyze $analyze): void
    {
        $analyze->update(['status' => 'completed']);
        foreach ($validated['result'] as $result) $analyze->results()->create($result);
    }

    /**
     * @param Analyze $analyze
     * @param array $validated
     * @return void
     */
    public function update(array $validated, Analyze $analyze): void
    {
        $analyze->update(['status' => 'completed']);
        foreach ($validated['result'] as $result) $analyze->results()->update($result);
    }

    /**
     * @param AnalyzeResult $result
     * @return void
     */
    public function destroy(AnalyzeResult $result): void
    {
        $result->delete();
    }
}
