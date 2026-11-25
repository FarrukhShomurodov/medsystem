<?php

namespace App\Services;

use App\Models\Diagnosis;
use App\Models\Survey;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DiagnosisService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Diagnosis
    {
        return Diagnosis::query()->create(Arr::only($validated, ['code', 'name']));
    }

    /**
     * The surveys picked on the form are the ones suggested for the diagnosis; the others lose the link.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Diagnosis $diagnosis, array $validated): Diagnosis
    {
        return DB::transaction(function () use ($diagnosis, $validated): Diagnosis {
            $diagnosis->update(Arr::only($validated, ['code', 'name']));

            Survey::query()->whereBelongsTo($diagnosis)->update(['diagnosis_id' => null]);
            Survey::query()->whereKey($validated['surveys'] ?? [])->update(['diagnosis_id' => $diagnosis->id]);

            return $diagnosis;
        });
    }

    public function destroy(Diagnosis $diagnosis): void
    {
        $diagnosis->delete();
    }
}
