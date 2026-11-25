<?php

use App\Enums\QuestionType;
use App\Models\Document;
use App\Models\DocumentAnswer;
use App\Models\DocumentQuestion;
use App\Models\DocumentResponse;
use App\Models\DocumentScore;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientDocumentScoreFinished;
use App\Services\Clinical\DocumentResponseService;
use Database\Seeders\DocumentSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| Medical documents (DocumentSeeder, fixed ids 1-8) filled in from the nurse window and the
| examination map pages, their stored total score and the actions recommended for it.
*/

beforeEach(function (): void {
    seed(DocumentSeeder::class);
    actingAs(adminWithPermissions('sister_window_show'), 'admin');
});

/**
 * The answers of a sheet keyed by question id: the given answers by wording, every other
 * question answered with a neutral value of its type.
 *
 * @param  array<string, string>  $byWording
 * @return array<int, string>
 */
function sheetAnswers(int $documentId, array $byWording = [], string $pick = 'first'): array
{
    return Document::query()->findOrFail($documentId)->questions
        ->mapWithKeys(fn (DocumentQuestion $question): array => [$question->id => $byWording[$question->question] ?? match ($question->type) {
            QuestionType::Choice => $pick === 'last' ? last($question->options) : $question->options[0],
            QuestionType::Boolean => $pick === 'last' ? '1' : '0',
            QuestionType::Text => 'без особенностей',
        }])
        ->all();
}

/**
 * @param  array<int, string>  $answers
 */
function fillSheet(Patient $patient, int $documentId, array $answers): DocumentResponse
{
    test()->post(route('document.response.store'), [
        'document_id' => $documentId,
        'patient_id' => $patient->id,
        'answers' => $answers,
        'previous_url' => url('/dashboard/sister-window/show/'.$patient->id),
    ])->assertRedirect(url('/dashboard/sister-window/show/'.$patient->id))
        ->assertSessionHas('success');

    return DocumentResponse::query()->latest('id')->firstOrFail();
}

it('scores the outpatient fall risk sheet with the yes of its checkboxes', function (): void {
    $answers = sheetAnswers(4, [
        'Чувствуете ли вы сейчас головокружение?' => '1',
        'Чувствуете слабость в ногах?' => '1',
        'Ходит с костылями/тростью/в инвалидном кресле' => '1',
    ]);

    // The old code counted "Да" while the form posts "1": every outpatient was stored with 0 (low risk).
    expect(fillSheet(Patient::factory()->create(), 4, $answers)->total_score)->toBe(3);
});

it('scores the pain sheet with the reported intensity', function (): void {
    $answers = sheetAnswers(5, ['Интенсивность боли (0-10)' => '8 - Жестокая боль']);

    // The old code read the answer at key 0, which never exists (answers are keyed by question id).
    expect(fillSheet(Patient::factory()->create(), 5, $answers)->total_score)->toBe(8);
});

it('scores the Humpty Dumpty sheet with age and sex from the patient record', function (): void {
    $boyOfTwo = Patient::factory()->create(['gender' => 'male', 'birth_date' => now()->subYears(2)->subMonth()->toDateString()]);

    // The sheet no longer asks for age and sex; the old code still scored the diagnosis as the age,
    // so every child got 5 points, below the lowest range of the sheet.
    expect(fillSheet($boyOfTwo, 3, sheetAnswers(3))->total_score)->toBe(23);
});

it('stores the highest total of every scored sheet for the highest-risk answers', function (int $documentId, string $pick, int $maximum): void {
    $boyOfTwo = Patient::factory()->create(['gender' => 'male', 'birth_date' => now()->subYears(2)->toDateString()]);

    // Every scored question of the calculator must exist with this wording in the seeded sheet.
    expect(fillSheet($boyOfTwo, $documentId, sheetAnswers($documentId, pick: $pick))->total_score)->toBe($maximum);
})->with([
    'Morse' => [2, 'last', 125],
    'Humpty Dumpty' => [3, 'first', 23],
    'outpatient (КДО)' => [4, 'last', 5],
    'pain intensity' => [5, 'last', 10],
    'FLACC' => [6, 'last', 10],
    'NIPS' => [7, 'last', 7],
    'CPOT' => [8, 'last', 8],
]);

it('stores the answers and attaches the response to the active examination map', function (): void {
    $map = ExaminationMap::factory()->create(['is_active' => true]);
    $answers = sheetAnswers(2, ['Падал ли в последние 3 мес.?' => 'Да']);

    $response = fillSheet($map->patient, 2, $answers);

    expect($response->total_score)->toBe(25)
        ->and($response->examination_map_id)->toBe($map->id)
        ->and($response->answers()->pluck('answer', 'question_id')->all())->toEqual($answers);
});

it('refuses an answer that is not an option of the question', function (): void {
    $patient = Patient::factory()->create();
    $answers = sheetAnswers(5, ['Интенсивность боли (0-10)' => '11 - хуже не бывает']);

    $this->post(route('document.response.store'), [
        'document_id' => 5,
        'patient_id' => $patient->id,
        'answers' => $answers,
        'previous_url' => url('/dashboard'),
    ])->assertSessionHasErrors('answers.'.array_key_first($answers));

    expect(DocumentResponse::query()->count())->toBe(0);
});

it('refuses answers to the questions of another document', function (): void {
    $patient = Patient::factory()->create();

    $this->post(route('document.response.store'), [
        'document_id' => 4,
        'patient_id' => $patient->id,
        'answers' => sheetAnswers(4) + sheetAnswers(2),
        'previous_url' => url('/dashboard'),
    ])->assertSessionHasErrors('answers');

    expect(DocumentAnswer::query()->count())->toBe(0);
});

it('does not redirect to another site after saving, but to the patient\'s page the account may open', function (): void {
    // The fallback was the patient card, a 403 for a nurse who works from the nurse window only.
    $patient = Patient::factory()->create();
    $save = fn () => $this->post(route('document.response.store'), [
        'document_id' => 4,
        'patient_id' => $patient->id,
        'answers' => sheetAnswers(4),
        'previous_url' => 'https://example.com/phishing',
    ]);

    $save()->assertRedirect(route('sister.window.show', $patient));

    actingAs(adminWithPermissions('sister_window_show', 'view_patients'), 'admin');

    $save()->assertRedirect(route('patients.show', $patient));
});

it('keeps the page the form was opened from through a refused submission', function (): void {
    // The form sent url()->previous(): after one refused submission that was the form itself, so the
    // saved document landed on a blank copy of the form, inviting a second one, and "Назад" looped.
    $patient = Patient::factory()->create();
    $window = route('sister.window.show', $patient);
    $form = route('documents.show', ['document' => 5, 'patient_id' => $patient->id]);
    $answers = sheetAnswers(5);
    $sheet = fn (array $answers): array => ['document_id' => 5, 'patient_id' => $patient->id, 'answers' => $answers, 'previous_url' => $window];

    $this->get($form, ['referer' => $window])->assertOk()->assertSeeHtml('name="previous_url" value="'.$window.'"');

    $this->from($form)
        // Blank once trimmed: it passes the browser's "required", not the server's.
        ->post(route('document.response.store'), $sheet(array_replace($answers, [array_key_first($answers) => '   '])))
        ->assertRedirect($form);

    // The browser asks for the form again with the form itself as the referer.
    $this->get($form, ['referer' => $form])
        ->assertOk()
        ->assertSeeHtml('name="previous_url" value="'.$window.'"')
        ->assertSeeHtml('<a href="'.$window.'" class="btn">Назад</a>');

    $this->post(route('document.response.store'), $sheet($answers))->assertRedirect($window);
});

it('links the answers of a document to the patient card, not to the previous page', function (): void {
    // The stay forms open the answers in a new tab, where "Назад" (url()->previous()) led to a
    // second copy of the stay form.
    $map = ExaminationMap::factory()->create();
    $answers = fillSheet($map->patient, Document::INITIAL_NURSING_ASSESSMENT, sheetAnswers(Document::INITIAL_NURSING_ASSESSMENT));
    $card = 'href="'.route('patients.show', $map->patient).'"';

    actingAs(adminWithPermissions('edit_maps', 'view_patients'), 'admin')
        ->get(route('documents.show.answer', $answers))
        ->assertOk()
        ->assertSeeHtml($card);

    actingAs(adminWithPermissions('edit_maps'), 'admin')
        ->get(route('documents.show.answer', $answers))
        ->assertOk()
        ->assertDontSeeHtml($card);
});

it('returns to the patient\'s page of the account when the form was not opened from another page', function (): void {
    $patient = Patient::factory()->create();
    $form = route('documents.show', ['document' => 5, 'patient_id' => $patient->id]);

    $this->get($form)->assertOk()->assertSeeHtml('name="previous_url" value="'.route('sister.window.show', $patient).'"');

    // Reopened from a bookmark: the last page of the session is the form itself.
    actingAs(adminWithPermissions('sister_window_show', 'view_patients'), 'admin')
        ->get($form)
        ->assertOk()
        ->assertSeeHtml('name="previous_url" value="'.route('patients.show', $patient).'"');
});

it('asks for the patient before opening a document form', function (): void {
    $this->from(url('/dashboard/sister-window'))
        ->get(route('documents.show', 2))
        ->assertRedirect(url('/dashboard/sister-window'))
        ->assertSessionHasErrors('patient_id');

    $this->get(route('documents.show', ['document' => 2, 'patient_id' => Patient::factory()->create()->id]))
        ->assertOk()
        ->assertSee('Падал ли в последние 3 мес.?');
});

describe('recommended actions in the nurse window', function (): void {
    /**
     * A filled-in sheet of an open stay: the nurse window shows only those, and the account of
     * these tests works in no department, so it reaches every open stay.
     */
    function stayResponse(int $documentId, int $totalScore, ?ExaminationMap $stay = null): DocumentResponse
    {
        return DocumentResponse::factory()
            ->onStay($stay ?? ExaminationMap::factory()->create())
            ->create(['document_id' => $documentId, 'total_score' => $totalScore]);
    }

    it('picks the range of the stored total, not of the response id', function (): void {
        $highRisk = stayResponse(2, 60);

        // The view passes the response id; the old code took it for the score (id 1: low risk).
        $this->getJson(route('api.document-scores.actions', [2, $highRisk]))
            ->assertOk()
            ->assertJsonPath('from_score', 51)
            ->assertJsonPath('to_score', null)
            ->assertJsonFragment(['action' => 'Совершать обходы в палату каждые 2 часа']);
    });

    it('picks the single-point range of the pain sheet', function (): void {
        $response = stayResponse(5, 8);

        // All pain ranges are open (to_score null), so the old unordered first() returned "Боли нет".
        $this->getJson(route('api.document-scores.actions', [5, $response]))
            ->assertOk()
            ->assertJsonPath('from_score', 8)
            ->assertJsonPath('actions.0.action', 'Жестокая боль');
    });

    it('pins the score ranges of every scored sheet', function (int $documentId, int $score, int $from): void {
        expect(DocumentScore::query()->where('document_id', $documentId)->containing($score)->first()?->from_score)
            ->toBe($from);
    })->with([
        'Morse 24' => [2, 24, 0],
        'Morse 25' => [2, 25, 25],
        'Morse 50' => [2, 50, 25],
        'Morse 51' => [2, 51, 51],
        'Morse 125' => [2, 125, 51],
        'Humpty Dumpty 11' => [3, 11, 7],
        'Humpty Dumpty 12' => [3, 12, 12],
        'Humpty Dumpty 23' => [3, 23, 12],
        'КДО 2' => [4, 2, 0],
        'КДО 3' => [4, 3, 3],
        'pain 0' => [5, 0, 0],
        'pain 10' => [5, 10, 10],
        'FLACC 0' => [6, 0, 0],
        'FLACC 3' => [6, 3, 1],
        'FLACC 4' => [6, 4, 4],
        'FLACC 7' => [6, 7, 7],
        'NIPS 3' => [7, 3, 0],
        'NIPS 4' => [7, 4, 4],
        'CPOT 1' => [8, 1, 0],
        'CPOT 2' => [8, 2, 2],
        'CPOT 6' => [8, 6, 6],
    ]);

    it('answers with an empty list for a total outside every range', function (): void {
        $response = stayResponse(1, 0);

        $this->getJson(route('api.document-scores.actions', [1, $response]))->assertOk()->assertExactJson([]);
    });

    it('refuses a response of another document', function (): void {
        $response = stayResponse(2, 60);

        $this->getJson(route('api.document-scores.actions', [5, $response]))->assertNotFound();
    });

    it('shows the actions done for this response only, and marks each one once', function (): void {
        $first = stayResponse(2, 60);
        $second = stayResponse(2, 70, $first->map);
        $range = DocumentScore::query()->where('document_id', 2)->containing(60)->firstOrFail();
        $action = $range->actions()->firstOrFail();

        $done = ['document_response_id' => $first->id, 'document_score_id' => $range->id, 'document_action_id' => $action->id];
        $this->postJson(route('api.document-actions.finish'), $done)->assertOk();
        $this->postJson(route('api.document-actions.finish'), $done)->assertOk();

        expect(PatientDocumentScoreFinished::query()->count())->toBe(1);

        $this->getJson(route('api.document-scores.actions', [2, $first]))->assertJsonCount(1, 'finished');
        $this->getJson(route('api.document-scores.actions', [2, $second]))->assertJsonCount(0, 'finished');
    });

    it('keeps one mark per action even when a double click inserts it twice at the same moment', function (): void {
        $response = stayResponse(2, 60);
        $range = DocumentScore::query()->where('document_id', 2)->containing(60)->firstOrFail();
        $mark = ['document_response_id' => $response->id, 'document_score_id' => $range->id, 'document_action_id' => $range->actions()->firstOrFail()->id];
        PatientDocumentScoreFinished::markDone($mark);

        // Two simultaneous requests both miss the row in firstOrCreate's select; the unique key
        // turns the second insert into a lookup of the first.
        DB::beginTransaction();
        expect(fn () => DB::table('patient_document_score_finisheds')->insert($mark))->toThrow(UniqueConstraintViolationException::class);
        DB::rollBack();

        expect(PatientDocumentScoreFinished::query()->createOrFirst($mark)->wasRecentlyCreated)->toBeFalse()
            ->and(PatientDocumentScoreFinished::query()->count())->toBe(1);
    });

    it('refuses to mark an action of another range as done', function (): void {
        $response = stayResponse(2, 60);
        $range = DocumentScore::query()->where('document_id', 2)->containing(60)->firstOrFail();
        $lowRiskAction = DocumentScore::query()->where('document_id', 2)->containing(0)->firstOrFail()->actions()->firstOrFail();

        $this->postJson(route('api.document-actions.finish'), [
            'document_response_id' => $response->id,
            'document_score_id' => $range->id,
            'document_action_id' => $lowRiskAction->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('document_action_id');
    });

    it('refuses a range the stored total does not fall into', function (): void {
        $highRisk = stayResponse(2, 60);
        $lowRisk = DocumentScore::query()->where('document_id', 2)->containing(0)->firstOrFail();

        // The range and its action were checked against each other and the document, so a
        // high-risk response could be marked with the low-risk actions the nurse never saw.
        $this->postJson(route('api.document-actions.finish'), [
            'document_response_id' => $highRisk->id,
            'document_score_id' => $lowRisk->id,
            'document_action_id' => $lowRisk->actions()->firstOrFail()->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('document_score_id');

        expect(PatientDocumentScoreFinished::query()->count())->toBe(0);
    });

    it('is closed to accounts without the nurse window permission', function (): void {
        $response = stayResponse(2, 60);

        actingAs(adminWithPermissions('view_patients'), 'admin');

        $this->getJson(route('api.document-scores.actions', [2, $response]))->assertForbidden();
        $this->postJson(route('api.document-actions.finish'), [])->assertForbidden();
        $this->post(route('document.response.store'), [])->assertForbidden();
    });
});

describe('deleting a filled-in document', function (): void {
    // The only delete button sits on the new-stay form and removes the initial nursing assessment
    // filled in before the admission. The endpoint deleted any response by id, closed stays
    // included, for anyone holding create_patient_map (the registrar role among them).
    beforeEach(function (): void {
        actingAs(adminWithPermissions('create_patient_map'), 'admin');
    });

    /**
     * A filled-in sheet with an answer and a completed recommended action.
     *
     * @param  array<string, mixed>  $attributes
     */
    function filledSheet(array $attributes): DocumentResponse
    {
        $response = DocumentResponse::query()->create(['patient_id' => Patient::factory()->create()->id, 'total_score' => 60, ...$attributes]);
        $question = Document::query()->findOrFail($response->document_id)->questions()->firstOrFail();
        $response->answers()->create(['question_id' => $question->id, 'answer' => '0']);

        $score = DocumentScore::query()->where('document_id', $response->document_id)->first();

        if ($score !== null && $score->actions()->exists()) {
            PatientDocumentScoreFinished::markDone([
                'document_response_id' => $response->id,
                'document_score_id' => $score->id,
                'document_action_id' => $score->actions()->firstOrFail()->id,
            ]);
        }

        return $response;
    }

    it('deletes the initial nursing assessment that no stay holds yet', function (): void {
        $assessment = filledSheet(['document_id' => Document::INITIAL_NURSING_ASSESSMENT]);

        $this->from('/dashboard')
            ->delete(route('documents.show.answer.delete', $assessment))
            ->assertRedirect('/dashboard')
            ->assertSessionHas('success');

        expect($assessment->fresh())->toBeNull()
            ->and(DocumentAnswer::query()->count())->toBe(0);
    });

    it('refuses to delete a sheet that belongs to a stay or is not the initial assessment', function (array $attributes): void {
        $sheet = filledSheet($attributes);
        $finished = PatientDocumentScoreFinished::query()->count();

        $this->delete(route('documents.show.answer.delete', $sheet))->assertForbidden();

        expect($sheet->fresh())->not->toBeNull()
            ->and($sheet->answers()->count())->toBe(1)
            ->and(PatientDocumentScoreFinished::query()->count())->toBe($finished);
    })->with([
        'the assessment of an open stay' => fn (): array => ['document_id' => Document::INITIAL_NURSING_ASSESSMENT, 'examination_map_id' => ExaminationMap::factory()->create()->id],
        'the assessment of a closed stay' => fn (): array => ['document_id' => Document::INITIAL_NURSING_ASSESSMENT, 'examination_map_id' => ExaminationMap::factory()->discharged()->create()->id],
        'a Morse sheet of a closed stay' => fn (): array => ['document_id' => 2, 'examination_map_id' => ExaminationMap::factory()->discharged()->create()->id],
        'an outpatient Morse sheet from the nurse window' => fn (): array => ['document_id' => 2],
    ]);

    it('keeps an assessment that a stay took after the form was opened', function (): void {
        // The new-stay form names the response; an admission saved meanwhile attaches it.
        $assessment = filledSheet(['document_id' => Document::INITIAL_NURSING_ASSESSMENT]);
        $asTheFormSawIt = $assessment->fresh();
        $assessment->update(['examination_map_id' => ExaminationMap::factory()->create()->id]);

        expect(app(DocumentResponseService::class)->deleteUnattachedAssessment($asTheFormSawIt))->toBeFalse()
            ->and($assessment->fresh())->not->toBeNull();
    });
});

describe('opening a filled-in document', function (): void {
    // The answers page opened any response by id for either permission of its route, so every
    // patient's sheets could be read by walking the ids. Its only links are the "Смотреть осмотр"
    // buttons of the stay forms, both to an initial nursing assessment.

    /** The initial nursing assessment, filled in like any sheet: it joins the patient's open stay. */
    function assessmentOf(Patient $patient): DocumentResponse
    {
        return fillSheet($patient, Document::INITIAL_NURSING_ASSESSMENT, sheetAnswers(Document::INITIAL_NURSING_ASSESSMENT, [
            'Осмотр ведется со слов' => 'Члена семьи/друга',
        ]));
    }

    /** The href of the "Смотреть осмотр" button of a stay form. */
    function assessmentLink(TestResponse $form): string
    {
        expect(preg_match('/<a href="([^"]+)"[^>]*>\s*<i [^>]*><\/i>Смотреть осмотр/u', (string) $form->getContent(), $link))->toBe(1);

        return $link[1];
    }

    it('opens the initial nursing assessment no stay holds yet to the new-stay form\'s permission', function (): void {
        $assessment = assessmentOf(Patient::factory()->create());

        actingAs(adminWithPermissions('create_patient_map'), 'admin')
            ->get(route('documents.show.answer', $assessment))
            ->assertOk()
            ->assertSeeText('Осмотр ведется со слов')
            ->assertSeeText('Члена семьи/друга');
    });

    it('opens the initial nursing assessment of an open stay to the edit form\'s permission', function (): void {
        $map = ExaminationMap::factory()->create();
        $assessment = assessmentOf($map->patient);

        expect($assessment->examination_map_id)->toBe($map->id);

        actingAs(adminWithPermissions('edit_maps'), 'admin')
            ->get(route('documents.show.answer', $assessment))
            ->assertOk()
            ->assertSeeText('Члена семьи/друга');
    });

    it('refuses the assessment of a closed stay, which stays readable on the patient card', function (): void {
        // The edit form of a closed stay no longer opens, so nothing links its assessment here; the
        // closed record is read on the patient card, under view_patients, not under edit_maps.
        $map = ExaminationMap::factory()->create();
        $assessment = assessmentOf($map->patient);
        $map->update(['is_active' => false, 'discharge_date' => today()->toDateString()]);

        actingAs(adminWithPermissions('create_patient_map', 'edit_maps', 'view_patients'), 'admin')
            ->get(route('documents.show.answer', $assessment))
            ->assertForbidden()
            ->assertSeeText('Госпитализация закрыта: осмотр можно посмотреть в карте пациента.');

        $this->post(route('api.patients.history.partial'), ['type' => 'document', 'id' => $assessment->id])
            ->assertOk()
            ->assertSeeText('Члена семьи/друга');
    });

    it('refuses every other sheet, even to an account holding both permissions', function (DocumentResponse $sheet): void {
        actingAs(adminWithPermissions('create_patient_map', 'edit_maps'), 'admin')
            ->get(route('documents.show.answer', $sheet))
            ->assertForbidden()
            ->assertSeeText('Открыть можно только первичный сестринский осмотр из формы госпитализации.');
    })->with([
        'a Morse sheet of an open stay' => fn (): DocumentResponse => fillSheet(ExaminationMap::factory()->create()->patient, 2, sheetAnswers(2)),
        'an outpatient fall-risk sheet from the nurse window' => fn (): DocumentResponse => fillSheet(Patient::factory()->create(), 4, sheetAnswers(4)),
    ]);

    it('refuses an account holding only the other form\'s permission, naming that form', function (string $permission, DocumentResponse $assessment, string $reason): void {
        // The new-stay form opens the answers in a new tab: once the stay takes the assessment,
        // reloading that tab names the edit form, not "only the initial assessment".
        actingAs(adminWithPermissions($permission), 'admin')
            ->get(route('documents.show.answer', $assessment))
            ->assertForbidden()
            ->assertSeeText($reason);
    })->with([
        'the assessment no stay holds, to edit_maps' => [
            'edit_maps',
            fn (): DocumentResponse => assessmentOf(Patient::factory()->create()),
            'Осмотр ещё не прикреплён к госпитализации и открывается из формы новой госпитализации или в карте пациента.',
        ],
        'the assessment of an open stay, to create_patient_map' => [
            'create_patient_map',
            fn (): DocumentResponse => assessmentOf(ExaminationMap::factory()->create()->patient),
            'Осмотр прикреплён к открытой госпитализации и открывается из формы её редактирования или в карте пациента.',
        ],
    ]);

    it('opens an earlier assessment of the same kind too, not only the one the form links', function (string $permission, DocumentResponse $earlier): void {
        // Both forms link the latest assessment. An earlier one is the same record of the same
        // patient, and the new-stay form links it again once the stay that took the latest closes.
        actingAs(adminWithPermissions($permission), 'admin')
            ->get(route('documents.show.answer', $earlier))
            ->assertOk()
            ->assertSeeText('Члена семьи/друга');
    })->with([
        'one no stay holds, while an open stay holds a later one, to create_patient_map' => ['create_patient_map', function (): DocumentResponse {
            $patient = Patient::factory()->create();
            $earlier = assessmentOf($patient);
            assessmentOf($patient)->update(['examination_map_id' => ExaminationMap::factory()->create(['patient_id' => $patient->id])->id]);

            return $earlier;
        }],
        'an earlier one of an open stay, to edit_maps' => ['edit_maps', function (): DocumentResponse {
            $map = ExaminationMap::factory()->create();
            $earlier = assessmentOf($map->patient);
            assessmentOf($map->patient);

            return $earlier;
        }],
    ]);

    it('opens the assessment the new-stay form links', function (): void {
        $patient = Patient::factory()->create();
        $assessment = assessmentOf($patient);
        actingAs(adminWithPermissions('create_patient_map'), 'admin');

        $link = assessmentLink($this->get(route('patient.map.create', $patient))->assertOk());

        expect($link)->toBe(route('documents.show.answer', $assessment));
        $this->get($link)->assertOk()->assertSeeText('Члена семьи/друга');
    });

    it('opens the assessment the edit form of an open stay links', function (): void {
        $map = ExaminationMap::factory()->create();
        $assessment = assessmentOf($map->patient);
        actingAs(adminWithPermissions('edit_maps'), 'admin');

        $link = assessmentLink($this->get(route('maps.edit', $map))->assertOk());

        expect($link)->toBe(route('documents.show.answer', $assessment));
        $this->get($link)->assertOk()->assertSeeText('Члена семьи/друга');
    });
});
