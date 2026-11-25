<?php

namespace App\Services\Clinical;

use App\Models\Document;
use App\Models\DocumentQuestion;
use App\Models\DocumentResponse;
use App\Models\Patient;
use App\Services\Clinical\Scoring\DocumentScoreCalculator;
use App\Services\Clinical\Scoring\MedicalScale;
use App\Services\Clinical\Scoring\PatientDemographics;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class DocumentResponseService
{
    public function __construct(private readonly DocumentScoreCalculator $calculator) {}

    /**
     * @param  array{patient_id: int, document_id: int, answers: array<int|string, string>}  $validated
     */
    public function store(array $validated): DocumentResponse
    {
        $patient = Patient::query()->findOrFail($validated['patient_id']);
        $document = Document::query()->with('questions')->findOrFail($validated['document_id']);
        $answers = $validated['answers'];

        return DB::transaction(function () use ($patient, $document, $answers): DocumentResponse {
            $response = DocumentResponse::query()->create([
                'patient_id' => $patient->id,
                'examination_map_id' => $patient->activeExaminationMap()->value('id'),
                'document_id' => $document->id,
                'total_score' => $this->score($document, $patient, $answers, now()),
            ]);

            $response->answers()->createMany(
                $document->questions
                    ->filter(fn (DocumentQuestion $question): bool => array_key_exists($question->id, $answers))
                    ->map(fn (DocumentQuestion $question): array => [
                        'question_id' => $question->id,
                        'answer' => $answers[$question->id],
                    ])
                    ->values()
                    ->all(),
            );

            return $response;
        });
    }

    /**
     * Deletes the initial nursing assessment filled in before the admission, with its answers and
     * completed actions (cascade), if no stay holds it yet (DocumentResponsePolicy). The check is
     * part of the DELETE itself: a stay opened meanwhile attaches the assessment in its own
     * transaction (ExaminationMapService::store()), and an attached sheet is never deleted.
     *
     * @return bool whether it was deleted
     */
    public function deleteUnattachedAssessment(DocumentResponse $response): bool
    {
        return DocumentResponse::query()->whereKey($response->getKey())->unattachedInitialAssessment()->delete() === 1;
    }

    /**
     * The total score of the sheet filled in with the answers on the given day; 0 for a document
     * without a scale. documents:rescore recomputes stored scores with it.
     *
     * @param  Document  $document  with its questions
     * @param  array<int|string, string>  $answers  keyed by question id
     */
    public function score(Document $document, Patient $patient, array $answers, CarbonInterface $assessedAt): int
    {
        $scale = MedicalScale::forDocument($document);

        if ($scale === null) {
            return 0;
        }

        $byWording = $document->questions
            ->filter(fn (DocumentQuestion $question): bool => array_key_exists($question->id, $answers))
            ->mapWithKeys(fn (DocumentQuestion $question): array => [$question->question => (string) $answers[$question->id]])
            ->all();

        return $this->calculator->calculate($scale, $byWording, PatientDemographics::of($patient, $assessedAt));
    }
}
