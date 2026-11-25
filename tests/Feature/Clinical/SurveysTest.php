<?php

use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyType;

use function Pest\Laravel\actingAs;

/*
| Surveys (the questionnaires of the "Мед. документы" catalogue) and the responses filled in on
| the patient card and on the survey page of an examination map.
*/

/**
 * A survey with one ungrouped question and a group of one question, both answered by a patient.
 *
 * @return array{survey: Survey, standalone: SurveyQuestion, grouped: SurveyQuestion}
 */
function answeredSurvey(): array
{
    $survey = Survey::factory()->create(['title' => 'Анкета перед операцией']);
    $group = $survey->groups()->create(['title' => 'Анамнез']);
    $standalone = $survey->questions()->create(['question' => 'Курите?', 'type' => 'boolean']);
    $grouped = $survey->questions()->create([
        'question' => 'Аллергия на анестезию?',
        'type' => 'choice',
        'options' => [['text' => 'Нет', 'score' => 0], ['text' => 'Да', 'score' => 1]],
        'group_id' => $group->id,
    ]);

    $response = SurveyResponse::query()->create(['patient_id' => Patient::factory()->create()->id, 'survey_id' => $survey->id]);
    $response->answers()->createMany([
        ['question_id' => $standalone->id, 'answer' => 'no'],
        ['question_id' => $grouped->id, 'answer' => 'Да'],
    ]);

    return compact('survey', 'standalone', 'grouped');
}

/**
 * The edit form as it posts the survey back, with the given changes.
 *
 * @return array<string, mixed>
 */
function surveyForm(Survey $survey, array $overrides = []): array
{
    $survey->load(['groups.questions', 'questions']);

    return [
        'survey_type_id' => $survey->survey_type_id,
        'title' => $survey->title,
        'color' => $survey->color,
        'questions' => $survey->questions->whereNull('group_id')->values()->map(fn (SurveyQuestion $question): array => [
            'id' => $question->id,
            'question' => $question->question,
            'type' => $question->type->value,
        ])->all(),
        'groups' => $survey->groups->map(fn ($group): array => [
            'id' => $group->id,
            'title' => $group->title,
            'questions' => $group->questions->map(fn (SurveyQuestion $question): array => [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type->value,
                'options' => $question->options,
            ])->all(),
        ])->all(),
        ...$overrides,
    ];
}

describe('editing a survey', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_surveys', 'create_surveys', 'edit_surveys'), 'admin');
    });

    it('keeps the patients answers when the survey is edited', function (): void {
        ['survey' => $survey, 'standalone' => $standalone, 'grouped' => $grouped] = answeredSurvey();

        $form = surveyForm($survey, ['title' => 'Анкета перед плановой операцией']);
        $form['questions'][0]['question'] = 'Курите ли вы?';

        // The old update deleted every group and question and created them again: the cascade
        // took every answer of every patient with it.
        $this->put(route('surveys.update', $survey), $form)
            ->assertRedirect(route('surveys.index'))
            ->assertSessionHas('success');

        expect(SurveyAnswer::query()->count())->toBe(2)
            ->and($standalone->fresh()->question)->toBe('Курите ли вы?')
            ->and($grouped->fresh()->group_id)->toBe($grouped->group_id)
            ->and($survey->fresh()->title)->toBe('Анкета перед плановой операцией');
    });

    it('adds new questions and groups next to the existing ones', function (): void {
        ['survey' => $survey] = answeredSurvey();

        $form = surveyForm($survey);
        $form['questions'][] = ['question' => 'Рост, см', 'type' => 'text'];
        $form['groups'][] = ['title' => 'Образ жизни', 'questions' => [['question' => 'Спорт?', 'type' => 'boolean']]];

        $this->put(route('surveys.update', $survey), $form)->assertSessionHasNoErrors();

        expect($survey->questions()->count())->toBe(4)
            ->and($survey->groups()->pluck('title')->sort()->values()->all())->toBe(['Анамнез', 'Образ жизни'])
            ->and(SurveyAnswer::query()->count())->toBe(2);
    });

    it('removes a question nobody has answered', function (): void {
        ['survey' => $survey] = answeredSurvey();
        $unanswered = $survey->questions()->create(['question' => 'Вес, кг', 'type' => 'text']);

        $this->put(route('surveys.update', $survey), surveyForm($survey, [
            'questions' => array_values(array_filter(
                surveyForm($survey)['questions'],
                fn (array $question): bool => $question['id'] !== $unanswered->id,
            )),
        ]))->assertSessionHasNoErrors();

        expect(SurveyQuestion::query()->whereKey($unanswered->id)->exists())->toBeFalse();
    });

    it('refuses to remove a question patients have answered and changes nothing', function (): void {
        ['survey' => $survey, 'grouped' => $grouped] = answeredSurvey();

        $this->from(route('surveys.edit', $survey))
            ->put(route('surveys.update', $survey), surveyForm($survey, ['title' => 'Новое название', 'groups' => []]))
            ->assertRedirect(route('surveys.edit', $survey))
            ->assertSessionHas('error', 'Вопрос «Аллергия на анестезию?» нельзя удалить: на него уже есть ответы пациентов.');

        expect($grouped->fresh())->not->toBeNull()
            ->and($survey->fresh()->title)->toBe('Анкета перед операцией')
            ->and(SurveyAnswer::query()->count())->toBe(2);
    });

    it('refuses the question ids of another survey', function (): void {
        ['survey' => $survey] = answeredSurvey();
        $foreign = Survey::factory()->create()->questions()->create(['question' => 'Чужой вопрос', 'type' => 'text']);

        $form = surveyForm($survey);
        $form['questions'][0]['id'] = $foreign->id;

        $this->put(route('surveys.update', $survey), $form)->assertSessionHasErrors('questions.0.id');

        expect($foreign->fresh()->question)->toBe('Чужой вопрос');
    });

    it('creates a survey of an existing type without creating a type', function (): void {
        $type = SurveyType::factory()->create();

        // The old store took every submitted type id for a new type name: is_int() is false for
        // form input, so each survey got a new type called "1", "2", ...
        $this->post(route('surveys.store'), [
            'survey_type_id' => (string) $type->id,
            'title' => 'Опросник качества сна',
            'color' => Survey::COLOR[0],
            'questions' => [['question' => 'Сколько часов вы спите?', 'type' => 'text']],
            'groups' => [['title' => 'Режим', 'questions' => [[
                'question' => 'Просыпаетесь ночью?',
                'type' => 'choice',
                'options' => [['text' => 'Редко', 'score' => 0], ['text' => 'Часто', 'score' => 2]],
            ]]]],
        ])->assertRedirect(route('surveys.index'));

        $survey = Survey::query()->sole();

        expect(SurveyType::query()->count())->toBe(1)
            ->and($survey->survey_type_id)->toBe($type->id)
            ->and($survey->questions()->count())->toBe(2)
            ->and($survey->groups()->sole()->questions()->sole()->options)->toBe([['text' => 'Редко', 'score' => 0], ['text' => 'Часто', 'score' => 2]]);
    });

    it('renders the list, the survey and the edit form', function (): void {
        ['survey' => $survey] = answeredSurvey();
        actingAs(adminWithPermissions('list_surveys', 'view_surveys', 'edit_surveys'), 'admin');

        $this->get(route('surveys.index'))->assertOk()->assertSee('Анкета перед операцией');
        $this->get(route('surveys.show', $survey))->assertOk()->assertSee('Аллергия на анестезию?');
        $this->get(route('surveys.edit', $survey))->assertOk()->assertSeeHtml('name="groups[0][id]"');
    });
});

describe('filling in a survey', function (): void {
    it('opens the survey page of an examination map and saves the answers to that map', function (): void {
        ['survey' => $survey, 'standalone' => $standalone, 'grouped' => $grouped] = answeredSurvey();
        $map = ExaminationMap::factory()->create();
        actingAs(adminWithPermissions('create_survey_response', 'store_survey_response'), 'admin');

        // The page used to answer 500: its form posted to the undefined route map.survey.store.
        $this->get(route('map.survey', $map))
            ->assertOk()
            ->assertSeeHtml(route('response.survey.store'));

        // The form sends the map, not the patient.
        $this->from(route('map.survey', $map))
            ->post(route('response.survey.store'), [
                'examination_map_id' => $map->id,
                'survey_id' => $survey->id,
                'answers' => [$standalone->id => 'yes', $grouped->id => 'Нет'],
            ])
            ->assertRedirect(route('map.survey', $map))
            ->assertSessionHas('success', 'Опросник заполнен!');

        $response = SurveyResponse::query()->latest('id')->firstOrFail();

        expect($response->only(['patient_id', 'examination_map_id']))->toBe(['patient_id' => $map->patient_id, 'examination_map_id' => $map->id])
            ->and($response->answers()->pluck('answer', 'question_id')->all())->toEqual([$standalone->id => 'yes', $grouped->id => 'Нет']);
    });

    it('links a response from the patient card to the service it documents', function (): void {
        ['survey' => $survey, 'standalone' => $standalone, 'grouped' => $grouped] = answeredSurvey();
        $service = PatientService::factory()->create();
        actingAs(adminWithPermissions('store_survey_response'), 'admin');

        $this->post(route('response.survey.store'), [
            'patient_id' => $service->patient_id,
            'patient_service_id' => $service->id,
            'survey_id' => $survey->id,
            'answers' => [$standalone->id => 'no', $grouped->id => 'Нет'],
        ])->assertSessionHasNoErrors();

        expect(SurveyResponse::query()->latest('id')->firstOrFail()->patient_service_id)->toBe($service->id);
    });

    it('refuses answers to the questions of another survey and services of another patient', function (): void {
        ['survey' => $survey, 'standalone' => $standalone] = answeredSurvey();
        $foreign = Survey::factory()->create()->questions()->create(['question' => 'Чужой вопрос', 'type' => 'text']);
        $patient = Patient::factory()->create();
        actingAs(adminWithPermissions('store_survey_response'), 'admin');

        $this->post(route('response.survey.store'), [
            'patient_id' => $patient->id,
            'patient_service_id' => PatientService::factory()->create()->id,
            'survey_id' => $survey->id,
            'answers' => [$standalone->id => 'no', $foreign->id => 'текст'],
        ])->assertSessionHasErrors(['answers', 'patient_service_id']);

        expect(SurveyResponse::query()->count())->toBe(1);
    });

    it('is closed to accounts that cannot fill in surveys', function (): void {
        $map = ExaminationMap::factory()->create();
        actingAs(adminWithPermissions('view_patients'), 'admin');

        $this->get(route('map.survey', $map))->assertForbidden();
        $this->post(route('response.survey.store'), [])->assertForbidden();
    });
});
