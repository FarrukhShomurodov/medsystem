<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentAnswer;
use App\Models\DocumentResponse;
use App\Services\Clinical\DocumentResponseService;
use App\Services\Clinical\Scoring\MedicalScale;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Recomputes the stored total score of the filled-in assessment sheets from their answers. 1.x
 * computed the scores wrongly (a "1" from a checkbox never counted as "yes", the pain intensity was
 * read from a key that never exists), and the nurse window chose the recommended actions by those
 * scores.
 */
#[Signature('documents:rescore {--force : Save the recomputed scores instead of only listing them}')]
#[Description('List the assessment sheets whose stored score differs from their answers; --force saves the recomputed scores')]
class RescoreDocuments extends Command
{
    private const int CHUNK = 500;

    public function handle(DocumentResponseService $documents): int
    {
        $sheets = Document::query()
            ->with('questions')
            ->whereKey(array_column(MedicalScale::cases(), 'value'))
            ->get()
            ->keyBy('id');

        /** @var list<array{id: int, document: string, patient: int, filled_in: string, stored: int, recomputed: int}> $changes */
        $changes = [];

        DocumentResponse::query()
            ->whereIn('document_id', $sheets->keys())
            ->with(['answers:id,document_response_id,question_id,answer', 'patient:id,birth_date,gender'])
            ->chunkById(self::CHUNK, /** @param  Collection<int, DocumentResponse>  $responses */ function (Collection $responses) use ($sheets, $documents, &$changes): void {
                foreach ($responses as $response) {
                    // Only responses to these sheets are loaded, and patient_id is a required foreign key.
                    $sheet = $sheets->get($response->document_id)
                        ?? throw new LogicException("Response {$response->id} is not one of the scored sheets.");
                    $patient = $response->patient
                        ?? throw new LogicException("Response {$response->id} has no patient.");
                    $assessedAt = $response->created_at ?? now();
                    $recomputed = $documents->score(
                        $sheet,
                        $patient,
                        $response->answers->mapWithKeys(fn (DocumentAnswer $answer): array => [$answer->question_id => $answer->answer])->all(),
                        $assessedAt,
                    );

                    if ($recomputed !== $response->total_score) {
                        $changes[] = [
                            'id' => $response->id,
                            'document' => $sheet->title,
                            'patient' => $response->patient_id,
                            'filled_in' => $assessedAt->format('d.m.Y H:i'),
                            'stored' => $response->total_score,
                            'recomputed' => $recomputed,
                        ];
                    }
                }
            });

        if ($changes === []) {
            $this->components->info('Every stored score matches the answers of its sheet.');

            return self::SUCCESS;
        }

        $this->table(['Response', 'Document', 'Patient', 'Filled in', 'Stored', 'Recomputed'], $changes);

        if (! $this->option('force')) {
            $this->components->warn('Dry run: '.count($changes).' scores would change. Run it with --force to save them.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes): void {
            foreach ($changes as $change) {
                DocumentResponse::query()->whereKey($change['id'])->update(['total_score' => $change['recomputed']]);
            }
        });

        $this->components->info('Saved '.count($changes).' recomputed scores.');

        return self::SUCCESS;
    }
}
