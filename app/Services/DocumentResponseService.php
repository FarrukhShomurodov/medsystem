<?php

namespace App\Services;

use App\Models\DocumentResponse;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class DocumentResponseService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): DocumentResponse
    {
        $documentResponse = DB::transaction(function () use ($validated) {
            $map = Patient::find($validated['patient_id'])->examinationMaps()->where('is_active', true);

            if ($map->exists()) {
                $validated['examination_map_id'] = $map->first()->id;
            }

            $documentResponse = DocumentResponse::query()->create($validated);

            foreach ($validated['answers'] as $index => $answer) {
                $documentResponse->answers()->create([
                    'question_id' => $index,
                    'answer' => $answer,
                ]);
            }
            return $documentResponse;
        });

        return $documentResponse;
    }
}
