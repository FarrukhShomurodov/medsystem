<?php

use App\Models\Admin;
use App\Models\Document;
use App\Models\DocumentResponse;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientAssignment;
use App\Models\PatientDiagnosis;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use App\Models\Staff;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Database\Eloquent\Builder;
use Tests\Feature\Frontend\RenderedPage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

/*
| The patient pages after the move to Tabler: the patient list, card and forms, the prescription
| form, the nursing documents, the examination maps (hospital stays) and the nurse window. They open
| over the demo hospital without the old template, keep their scripts in page modules
| (resources/js/pages) and hand them the endpoints through route() in the markup.
*/

/** Markup of the removed template, inline styles and handlers, and the icon sets that were replaced by Boxicons. */
const PATIENT_PAGES_OLD_MARKUP = [
    'input-group-merge', 'icon-border', 'breadcrumb-wrapper', 'card-datatable', 'datatables-users',
    'btn-label-', 'bg-label-', 'form-group', 'btn-close-white', 'avatar-initial', 'switch-input',
    'errors_alert', 'basic-icon-default', 'class="bi ', 'class="fa ', 'class="fas ', 'class="far ', 'style="', '<style',
];

/** The page's own markup: what <main> holds, without the layout around it. */
function patientsMain(string $html): string
{
    return preg_match('#<main\b.*?</main>#s', $html, $match) === 1 ? $match[0] : '';
}

/** Problems of one rendered page of the group, as "page: problem" lines. */
function patientsPageProblems(string $name, string $html): array
{
    $main = patientsMain($html);
    $found = [
        ...RenderedPage::find($html, RenderedPage::TEMPLATE_PATTERNS),
        ...RenderedPage::find($html, RenderedPage::CDN_PATTERNS),
        ...array_filter(PATIENT_PAGES_OLD_MARKUP, fn (string $pattern): bool => str_contains($main, $pattern)),
    ];

    if ($main === '') {
        $found[] = 'no <main>';
    }

    if (preg_match('/<[^>]+\son[a-z]+\s*=/i', $main) === 1) {
        $found[] = 'inline event handler';
    }

    // Behaviour lives in resources/js/pages/*: the pages carry data blocks, never inline code.
    if (RenderedPage::inlineScripts($main) !== []) {
        $found[] = 'inline script';
    }

    return array_map(fn (string $problem): string => "{$name}: {$problem}", $found);
}

it('renders the patient, examination map and nurse pages without the old template or inline scripts', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);

    $admin = Admin::query()->where('email', 'admin@medsystem.test')->firstOrFail();
    actingAs($admin, 'admin');

    $stay = ExaminationMap::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    $outpatient = Patient::query()
        ->whereDoesntHave('examinationMaps', fn (Builder $map) => $map->where('is_active', true))
        ->orderBy('id')
        ->firstOrFail();
    $prescription = PatientMedication::query()->get()->firstOrFail(fn (PatientMedication $line): bool => ! $line->status->isFinal());
    // The answers page opens only the initial nursing assessment a stay form links, which the demo
    // does not fill in: this one is linked by the edit form of the stay below.
    $answers = DocumentResponse::factory()->create([
        'patient_id' => $stay->patient_id,
        'examination_map_id' => $stay->id,
        'document_id' => Document::INITIAL_NURSING_ASSESSMENT,
    ]);

    $pages = [
        'dashboard' => route('dashboard'),
        'patients.index' => route('patients.index'),
        'patients.index (filtered)' => route('patients.index', ['first_name' => 'а', 'department_id' => $stay->department_id]),
        'patients.show (in hospital)' => route('patients.show', $stay->patient_id),
        'patients.show (outpatient)' => route('patients.show', $outpatient),
        'patients.create' => route('patients.create'),
        'patients.edit' => route('patients.edit', $stay->patient_id),
        'patient.medication.edit' => route('patient.medication.edit', $prescription),
        'documents.show' => route('documents.show', ['document' => Document::INITIAL_NURSING_ASSESSMENT, 'patient_id' => $stay->patient_id]),
        'documents.show.answer' => route('documents.show.answer', $answers),
        'patient.map.create' => route('patient.map.create', $outpatient),
        'maps.show' => route('maps.show', $stay),
        'maps.edit' => route('maps.edit', $stay),
        'map.survey' => route('map.survey', $stay),
        'sister.window.index' => route('sister.window.index'),
        'sister.window.show' => route('sister.window.show', $stay->patient_id),
    ];

    $problems = [];
    $rendered = [];

    foreach ($pages as $name => $url) {
        $response = get($url);

        if ($response->getStatusCode() !== 200) {
            $problems[] = "{$name}: status {$response->getStatusCode()}";

            continue;
        }

        $rendered[$name] = (string) $response->getContent();
        $problems = [...$problems, ...patientsPageProblems($name, $rendered[$name])];
    }

    // The front desk registers a patient on the short form (patients/register).
    $register = actingAs(Admin::query()->where('email', 'registrar@medsystem.test')->firstOrFail(), 'admin')
        ->get(route('patients.create'));
    $register->assertOk()->assertSeeHtml('action="'.route('patients.store').'"');
    $rendered['patients.create (registrar)'] = (string) $register->getContent();
    $problems = [...$problems, ...patientsPageProblems('patients.create (registrar)', $rendered['patients.create (registrar)'])];

    expect($problems)->toBe([]);

    if (! RenderedPage::nodeAvailable()) {
        $this->markTestIncomplete('Node is not installed: the inline scripts were not parsed.');
    }

    expect(RenderedPage::unparsableScripts($rendered))->toBe([]);
});

it('hands the page modules their endpoints and data through the markup', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);
    actingAs(Admin::query()->where('email', 'admin@medsystem.test')->firstOrFail(), 'admin');

    $stay = ExaminationMap::query()->where('is_active', true)->orderBy('id')->firstOrFail();

    // patient-card.js: no URL is written in the script; ids it learns later replace __placeholders__.
    get(route('patients.show', $stay->patient_id))
        ->assertOk()
        ->assertSeeHtml('data-history-url="'.route('api.patients.history.partial').'"')
        ->assertSeeHtml('data-substance-url="'.route('api.medications.by-active-substance', ['substance' => '__substance__']).'"')
        ->assertSeeHtml('data-questions-url="'.route('api.surveys.questions', ['survey' => '__survey__']).'"')
        ->assertSeeHtml('data-store-url="'.route('patient.diagnoses.store').'"')
        ->assertSeeHtml('<template id="medication-template">')
        ->assertSeeHtml('<template id="procedure-template">');

    // nurse-window.js reads the measurements from a JSON block (no CDN chart library any more).
    $nurse = get(route('sister.window.show', $stay->patient_id))->assertOk();
    expect((string) $nurse->getContent())
        ->toContain('<script type="application/json" id="vital-signs-data">')
        ->toContain('data-actions-url="'.route('api.document-scores.actions', ['document' => '__document__', 'response' => '__response__']).'"')
        ->not->toContain('apexcharts.min.js');

    get(route('map.survey', $stay))
        ->assertOk()
        ->assertSeeHtml('data-questions-url="'.route('api.surveys.questions', ['survey' => '__survey__']).'"')
        ->assertSeeHtml('id="responseModal"');
});

it('renders every history partial of the patient card without scripts or old icons', function (): void {
    seed(DatabaseSeeder::class);
    seed(DemoSeeder::class);
    actingAs(Admin::query()->where('email', 'admin@medsystem.test')->firstOrFail(), 'admin');

    $stay = ExaminationMap::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    $partials = [
        'examination_map_start' => $stay->id,
        'examination_map_end' => $stay->id,
        'service' => PatientService::query()->orderBy('id')->value('id'),
        'diagnosis' => PatientDiagnosis::query()->orderBy('id')->value('id'),
        'procedure' => PatientProcedure::query()->orderBy('id')->value('id'),
        'medication' => PatientMedication::query()->orderBy('id')->value('id'),
        'reception' => Reception::query()->orderBy('id')->value('id'),
        'document' => DocumentResponse::query()->orderBy('id')->value('id'),
        'patient_med_document' => SurveyResponse::query()->orderBy('id')->value('id'),
        'assignment' => PatientAssignment::query()->create([
            'patient_id' => $stay->patient_id,
            'service_id' => PatientService::query()->value('service_id'),
            'staff_id' => Staff::query()->value('id'),
            'comment' => 'Проверка <b>разметки</b>',
        ])->id,
    ];

    $problems = [];

    foreach ($partials as $type => $id) {
        if ($id === null) {
            $problems[] = "{$type}: no demo record";

            continue;
        }

        $response = post(route('api.patients.history.partial'), ['type' => $type, 'id' => $id]);
        $html = (string) $response->getContent();

        if ($response->getStatusCode() !== 200) {
            $problems[] = "{$type}: status {$response->getStatusCode()}";

            continue;
        }

        foreach (['<script', ' style="', ' onclick=', 'class="bi ', 'class="fa ', 'class="fas ', 'avatar-initial', '<b>разметки'] as $pattern) {
            if (str_contains($html, $pattern)) {
                $problems[] = "{$type}: {$pattern}";
            }
        }
    }

    expect($problems)->toBe([]);
});

it('has a colour class for every colour a medical document can be marked with', function (): void {
    // maps/show marks a document row with class="survey-color-<hex>" instead of a style attribute.
    $scss = (string) file_get_contents(resource_path('scss/pages/_patients.scss'));

    $missing = array_values(array_filter(
        Survey::COLOR,
        fn (string $color): bool => ! str_contains($scss, "'".strtolower(ltrim($color, '#'))."': ".strtolower($color)),
    ));

    expect($missing)->toBe([]);
});
