<?php

use App\Models\Document;
use App\Models\DocumentAnswer;
use App\Models\DocumentResponse;
use App\Models\Patient;
use Database\Seeders\DocumentSeeder;

use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

/*
| documents:rescore recomputes the total scores stored by 1.x, where a checkbox "1" never counted
| as "yes" and the pain intensity was never read: those sheets were stored with 0.
*/

beforeEach(function (): void {
    seed(DocumentSeeder::class);
});

/**
 * A sheet filled in on the given day with the answers by question wording, stored with the score.
 *
 * @param  array<string, string>  $answers
 */
function storedSheet(int $documentId, array $answers, int $storedScore, string $filledIn, ?Patient $patient = null): DocumentResponse
{
    $response = DocumentResponse::factory()->create([
        'patient_id' => ($patient ?? Patient::factory()->create())->id,
        'document_id' => $documentId,
        'total_score' => $storedScore,
        'created_at' => $filledIn,
    ]);
    $questions = Document::query()->findOrFail($documentId)->questions->pluck('id', 'question');

    foreach ($answers as $question => $answer) {
        DocumentAnswer::query()->create(['document_response_id' => $response->id, 'question_id' => $questions[$question], 'answer' => $answer]);
    }

    return $response;
}

beforeEach(function (): void {
    $this->outpatient = storedSheet(4, [
        'Чувствуете ли вы сейчас головокружение?' => '1',
        'Чувствуете слабость в ногах?' => '1',
        'Ходит с костылями/тростью/в инвалидном кресле' => '0',
    ], storedScore: 0, filledIn: '2025-05-10 09:30:00');

    $this->pain = storedSheet(5, ['Интенсивность боли (0-10)' => '8 - Жестокая боль'], storedScore: 0, filledIn: '2025-05-11 14:00:00');

    // Age and sex come from the patient record, the age as it was on the day of the assessment:
    // two years old then (4 points), six now (3 points); a boy (2 points).
    $toddler = Patient::factory()->create(['birth_date' => '2020-01-15', 'gender' => 'male']);
    $this->humptyDumpty = storedSheet(3, [], storedScore: 0, filledIn: '2022-06-01 08:00:00', patient: $toddler);

    $this->correct = storedSheet(5, ['Интенсивность боли (0-10)' => '3 - Умеренная боль'], storedScore: 3, filledIn: '2025-06-01 10:00:00');
    $this->unscored = storedSheet(Document::INITIAL_NURSING_ASSESSMENT, [], storedScore: 0, filledIn: '2025-06-01 10:00:00');
});

it('lists the sheets whose stored score differs from their answers without changing them', function (): void {
    $title = fn (int $id): string => Document::query()->findOrFail($id)->title;

    artisan('documents:rescore')
        ->expectsTable(['Response', 'Document', 'Patient', 'Filled in', 'Stored', 'Recomputed'], [
            [$this->outpatient->id, $title(4), $this->outpatient->patient_id, '10.05.2025 09:30', 0, 2],
            [$this->pain->id, $title(5), $this->pain->patient_id, '11.05.2025 14:00', 0, 8],
            [$this->humptyDumpty->id, $title(3), $this->humptyDumpty->patient_id, '01.06.2022 08:00', 0, 6],
        ])
        ->expectsOutputToContain('Dry run: 3 scores would change.')
        ->assertSuccessful();

    expect(DocumentResponse::query()->orderBy('id')->pluck('total_score')->all())->toBe([0, 0, 0, 3, 0]);
});

it('saves the recomputed scores with --force, after which nothing is left to do', function (): void {
    artisan('documents:rescore', ['--force' => true])
        ->expectsOutputToContain('Saved 3 recomputed scores.')
        ->assertSuccessful();

    expect($this->outpatient->fresh()->total_score)->toBe(2)
        ->and($this->pain->fresh()->total_score)->toBe(8)
        ->and($this->humptyDumpty->fresh()->total_score)->toBe(6)
        ->and($this->correct->fresh()->total_score)->toBe(3)
        ->and($this->unscored->fresh()->total_score)->toBe(0);

    artisan('documents:rescore')
        ->expectsOutputToContain('Every stored score matches the answers of its sheet.')
        ->assertSuccessful();
});
