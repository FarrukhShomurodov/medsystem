<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentResponse;
use App\Models\ExaminationMap;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A filled-in medical document. Its answers are left out: the total score is what the nurse
 * window reads.
 *
 * @extends Factory<DocumentResponse>
 */
class DocumentResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'document_id' => Document::factory(),
            'total_score' => 0,
        ];
    }

    /** Filled in during the stay, for its patient, as DocumentResponseService::store() attaches it. */
    public function onStay(ExaminationMap $stay): static
    {
        return $this->state(fn (): array => ['patient_id' => $stay->patient_id, 'examination_map_id' => $stay->id]);
    }
}
