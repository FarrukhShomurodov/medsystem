<?php

use App\Models\DangerousCombination;
use App\Models\Diagnosis;
use App\Models\ExaminationMap;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientDiagnosis;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Service;
use App\Models\Staff;
use App\Models\VitalParameter;
use App\Queries\Billing\BillableItem;
use App\Queries\Patients\PatientTimeline;
use App\Services\Billing\BillableType;
use App\Services\Patients\RecordPatientView;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Frontend\RenderedPage;

use function Pest\Laravel\actingAs;

/** A hospital stay with a record of every kind, plus outpatient records. */
function patientWithHistory(int $stays = 1): Patient
{
    $patient = Patient::factory()->create();

    ExaminationMap::factory()->count($stays)->discharged()->for($patient)->create()
        ->each(function (ExaminationMap $map) use ($patient): void {
            PatientService::factory()->for($patient)->create(['examination_map_id' => $map->id]);
            PatientDiagnosis::factory()->create(['patient_id' => $patient->id, 'examination_map_id' => $map->id]);
            PatientProcedure::factory()->for($patient)->create(['examination_map_id' => $map->id]);
            PatientMedication::factory()->for($patient)->create(['examination_map_id' => $map->id]);
        });

    PatientService::factory()->for($patient)->create();
    PatientProcedure::factory()->for($patient)->create();
    PatientMedication::factory()->for($patient)->create();

    return $patient;
}

/** Where the back button in the header of the card leads, by its label; null without one. */
function cardBackLink(TestResponse $response, string $label): ?string
{
    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="utf-8"?>'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $link = (new DOMXPath($document))->query('//a[contains(normalize-space(.), "'.$label.'")]/@href')->item(0);

    return $link?->nodeValue;
}

it('renders the card of a patient with stays, prescriptions and diagnoses', function (): void {
    $patient = patientWithHistory();
    // A medication without composition data crashed the prescription form of the card.
    PatientMedication::factory()->for($patient)->create(['medication_id' => Medication::factory()->create()->id]);

    actingAs(adminWithPermissions('view_patients', 'create_patient_medication', 'list_patient_medication', 'list_patient_diagnoses'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->assertSee('Госпитализация (начало)')
        ->assertSee('Госпитализация (конец)');
});

it('holds the records of the stays in the patient relations, and lists each one only under its stay', function (): void {
    // The patient relations left the records of the stays out under general names (services,
    // diagnosises), a trap for any other reader of them.
    $patient = patientWithHistory(stays: 2);
    PatientDiagnosis::factory()->create(['patient_id' => $patient->id]);

    expect($patient->services()->count())->toBe(3)
        ->and($patient->diagnoses()->count())->toBe(3)
        ->and($patient->patientProcedures()->count())->toBe(3)
        ->and($patient->patientMedications()->count())->toBe(3);

    $events = collect(app(PatientTimeline::class)->for($patient))
        ->filter(fn (array $event): bool => in_array($event['type'], ['service', 'diagnosis', 'procedure', 'medication'], true));

    expect($events)->toHaveCount(12)
        ->and($events->map(fn (array $event): string => $event['type'].':'.$event['data']->getKey())->duplicates())->toBeEmpty()
        ->and($events->every(fn (array $event): bool => $event['parent_id'] === $event['data']->getAttribute('examination_map_id')))->toBeTrue()
        ->and($events->where('is_main', true))->toHaveCount(4);
});

it('titles every row of the history in Russian, a service with its own name', function (): void {
    // A service row printed its raw type, "service".
    $patient = patientWithHistory();
    $service = PatientService::factory()->for($patient)->create([
        'service_id' => Service::factory()->create(['name' => 'УЗИ щитовидной железы'])->id,
    ]);

    $card = actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->assertSeeText('Услуга: УЗИ щитовидной железы')
        ->assertSeeHtml('<span class="badge '.$service->status->badgeClass().' ms-1">'.$service->status->label().'</span>');

    expect(strip_tags((string) $card->getContent()))->not->toMatch('/^\s*(service|procedure|medication|diagnosis)\s*$/m');
});

it('prints the patient\'s name in one order on the card, the patient list and the nurse window', function (): void {
    // The card printed "Фамилия Имя Отчество", the nurse window "Имя Фамилия Отчество".
    $patient = Patient::factory()->create(['last_name' => 'Каримова', 'first_name' => 'Дильноза', 'middle_name' => 'Рустамовна']);
    ExaminationMap::factory()->for($patient)->create();

    expect($patient->full_name)->toBe('Каримова Дильноза Рустамовна');

    actingAs(adminWithPermissions('view_patients', 'list_patients', 'sister_window_show'), 'admin');

    $this->get(route('patients.show', $patient))->assertOk()->assertSeeText('Каримова Дильноза Рустамовна');
    $this->get(route('patients.index'))->assertOk()->assertSeeText('Каримова Дильноза Рустамовна');
    $this->get(route('sister.window.show', $patient))->assertOk()
        ->assertSeeText('Каримова Дильноза Рустамовна')
        ->assertDontSeeText('Дильноза Каримова');
});

it('warns on the card about dangerous combinations of the prescriptions in progress only', function (): void {
    // The card matched the names exactly and case-sensitively against every prescription ever made,
    // so it missed "Варфарин" + "ибупрофен" and still warned about cancelled drugs.
    $patient = Patient::factory()->create();
    $prescribe = fn (string $name, string $status) => PatientMedication::factory()->for($patient)->create([
        'medication_id' => Medication::factory()->create(['name' => $name])->id,
        'status' => $status,
    ]);
    $prescribe('ВАРФАРИН', 'ordered');
    $prescribe('Ибупрофен', 'sent_to_floor');
    $prescribe('Метотрексат', 'cancelled');
    DangerousCombination::factory()->create(['drug1_ru' => 'Варфарин', 'drug2_ru' => 'ибупрофен', 'severity_ru' => 'Высокая']);
    DangerousCombination::factory()->create(['drug1_ru' => 'Ибупрофен', 'drug2_ru' => 'Метотрексат', 'severity_ru' => 'Высокая']);

    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->assertViewHas('warnings', fn ($warnings): bool => $warnings->pluck('drug2_ru')->all() === ['ибупрофен']);
});

it('asks the prescribing form for what the server stores: units and no status', function (): void {
    // The form offered a status the server ignores and a free-text dosage it refuses (an integer of units).
    actingAs(adminWithPermissions('view_patients', 'create_patient_medication'), 'admin')
        ->get(route('patients.show', Patient::factory()->create()))
        ->assertOk()
        ->assertDontSeeHtml('name="statuses[]"')
        ->assertSeeHtml('<input type="number" name="dosages[]"')
        ->assertSeeHtml('maxlength="255"></textarea>');
});

it('loads the card with the same number of queries however long the history is', function (): void {
    // The history ran two queries per stay from Blade, the list of recent patients four per row,
    // and every eager load ran its query twice.
    $viewer = adminWithPermissions('view_patients');
    $queries = function (Patient $patient) use ($viewer): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        actingAs($viewer, 'admin')->get(route('patients.show', $patient))->assertOk();

        return count(DB::getQueryLog());
    };

    $queries(patientWithHistory());

    expect($queries(patientWithHistory(stays: 4)))->toBe($queries(patientWithHistory(stays: 1)));
});

it('keeps the ten most recently opened patients, the latest first', function (): void {
    // The count ran on the relation already narrowed to the opened patient, so it was always 0
    // and the list was never trimmed.
    $viewer = adminWithPermissions('view_patients');
    $patients = Patient::factory()->count(12)->create();
    $record = app(RecordPatientView::class);

    $patients->each(fn (Patient $patient) => $record($viewer, $patient));
    $this->travel(1)->minutes();
    $record($viewer, $patients[3]);

    $kept = DB::table('last_viewed_patients')->where('admin_id', $viewer->id)
        ->orderByDesc('updated_at')->orderByDesc('id')->pluck('patient_id');

    expect($kept)->toHaveCount(RecordPatientView::KEEP)
        ->and($kept->first())->toBe($patients[3]->id)
        ->and($kept)->not->toContain($patients[0]->id, $patients[1]->id);
});

it('records a patient once per account, also when two cards are opened at the same moment', function (): void {
    // "Update, and insert when nothing was updated" let two simultaneous requests both insert.
    $viewer = adminWithPermissions('view_patients');
    $patient = Patient::factory()->create();
    $record = app(RecordPatientView::class);

    $record($viewer, $patient);
    $record($viewer, $patient);

    // The database itself refuses the second row a concurrent request would insert.
    DB::beginTransaction();
    expect(fn () => DB::table('last_viewed_patients')->insert(['admin_id' => $viewer->id, 'patient_id' => $patient->id]))
        ->toThrow(QueryException::class);
    DB::rollBack();

    expect(DB::table('last_viewed_patients')->where('admin_id', $viewer->id)->count())->toBe(1);
});

it('lists the recently opened patients on the card', function (): void {
    $viewer = adminWithPermissions('view_patients');
    [$first, $second] = Patient::factory()->count(2)->create();

    actingAs($viewer, 'admin')->get(route('patients.show', $first))->assertOk();

    expect(actingAs($viewer, 'admin')->get(route('patients.show', $second))->viewData('lastViews')->pluck('id')->all())
        ->toEqualCanonicalizing([$first->id, $second->id]);
});

it('shows the normal values of the opened patient to staff who may edit them', function (): void {
    // The form read $patient left over from the recent-patients loop: another patient's values.
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);
    $patient = Patient::factory()->create();
    $patient->vitalNormalParameters()->attach($pulse->id, ['normal' => 72]);
    $other = Patient::factory()->create();
    $other->vitalNormalParameters()->attach($pulse->id, ['normal' => 95]);
    $editor = adminWithPermissions('view_patients', 'edit_patients');

    actingAs($editor, 'admin')->get(route('patients.show', $other));

    actingAs($editor, 'admin')
        ->get(route('patients.show', $patient))
        ->assertSee('Нормальные показатели пациента')
        ->assertSeeHtml('value="72.0"')
        ->assertDontSeeHtml('value="95.0"');

    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->assertDontSee('Нормальные показатели пациента');
});

it('offers the referral form only to staff who may create referrals', function (): void {
    // The referral modal had no button that opened it.
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('view_patients', 'create_patient_services'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertSeeHtml('data-bs-target="#createMapModal"')
        ->assertSeeHtml(route('patient.assignment.store'));

    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertDontSeeHtml('data-bs-target="#createMapModal"')
        ->assertDontSeeHtml(route('patient.assignment.store'));
});

it('renders the medical document form only for staff who may fill in documents', function (): void {
    // The form posts to response.survey.store, which needs store_survey_response: everyone else got a 403.
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->assertDontSeeHtml(route('response.survey.store'));

    actingAs(adminWithPermissions('view_patients', 'store_survey_response'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertSeeHtml(route('response.survey.store'));
});

it('hides the links to the patient list from staff who may not open it', function (): void {
    $patient = Patient::factory()->create();

    $listLinks = ['href="'.route('patients.index').'"', 'action="'.route('patients.index').'"'];

    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->assertDontSeeHtml($listLinks);

    actingAs(adminWithPermissions('view_patients', 'list_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertSeeHtml($listLinks);
});

it('puts assigned procedures into the registration total', function (): void {
    // The procedures were read through a many-to-many relation without the status column, so
    // they never reached the list or the total.
    $patient = Patient::factory()->create();
    PatientService::factory()->for($patient)->create(['status' => 'assigned']);
    PatientProcedure::factory()->for($patient)->create(['procedure_id' => Procedure::factory()->create(['price' => 120000])->id]);
    PatientProcedure::factory()->for($patient)->confirmed()->create();

    $records = actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertSee('Итого:')
        ->viewData('allRecords');

    expect($records->map(fn (BillableItem $item): BillableType => $item->type)->all())->toEqualCanonicalizing([BillableType::Procedure, BillableType::Service])
        ->and($records->firstWhere('type', BillableType::Procedure)->price->toDecimal())->toBe('120000.00');
});

it('renders only the viewer\'s favourite diagnoses, the catalogue is searched on demand', function (): void {
    // The picker printed the whole ICD-10 catalogue into every card: about 17 MB of markup and
    // 28 000 buttons once the ministry's sheet (14 000 codes) was imported.
    $viewer = adminWithPermissions('view_patients', 'create_patient_diagnoses');
    $favourite = Diagnosis::factory()->create(['code' => 'J20.9', 'name' => 'Острый бронхит неуточнённый']);
    $other = Diagnosis::factory()->create(['code' => 'K29.5', 'name' => 'Хронический гастрит неуточнённый']);
    DB::table('admin_selected_diagnoses')->insert(['admin_id' => $viewer->id, 'diagnosis_id' => $favourite->id]);
    DB::table('admin_selected_diagnoses')->insert(['admin_id' => adminWithPermissions('create_patient_diagnoses')->id, 'diagnosis_id' => $other->id]);

    $response = actingAs($viewer, 'admin')->get(route('patients.show', Patient::factory()->create()))
        ->assertOk()
        ->assertSee('Острый бронхит неуточнённый')
        ->assertDontSee('Хронический гастрит неуточнённый')
        ->assertSeeHtml('data-search-url="'.e(route('api.diagnoses.search')).'"');

    expect($response->viewData('favoriteDiagnoses')->pluck('id')->all())->toBe([$favourite->id])
        ->and($response->viewData())->not->toHaveKey('regularDiagnoses');
});

it('offers to extend, change or cancel a prescription only to staff who may, while it is in progress', function (): void {
    // The buttons were shown to everyone who sees the sheet (their routes need edit_patient_medication)
    // and for given or returned prescriptions, which the server refuses.
    $patient = Patient::factory()->create();
    $inProgress = PatientMedication::factory()->for($patient)->create(['status' => 'ordered']);
    $given = PatientMedication::factory()->for($patient)->create(['status' => 'completed']);
    $card = fn (string ...$permissions) => actingAs(adminWithPermissions('view_patients', 'list_patient_medication', ...$permissions), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk();

    $card()->assertDontSee(['Продлить', 'Отменить']);

    $editor = $card('edit_patient_medication');
    expect(substr_count($editor->getContent(), 'Продлить'))->toBe(1)
        ->and(substr_count($editor->getContent(), 'Отменить'))->toBe(1);
    $editor->assertSeeHtml(route('patient.medication.edit', $inProgress->id))
        ->assertDontSeeHtml(route('patient.medication.edit', $given->id));
});

it('lists the recently opened patients with the stay\'s admission date and doctor, as the patient list does', function (): void {
    // The table printed the patient's created_at under "Дата поступления", the doctor's first name
    // only, and two columns that were a dash in every row.
    $doctor = Staff::factory()->create(['last_name' => 'Соколов', 'name' => 'Арсений', 'middle_name' => 'Петрович']);
    $patient = Patient::factory()->create(['created_at' => now()->subYear(), 'birth_date' => '1984-03-09']);
    ExaminationMap::factory()->for($patient)->create(['staff_id' => $doctor->id, 'admission_date' => today()->subDays(3)->toDateString()]);

    $html = (string) actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->getContent();
    [$row] = RenderedPage::tableRows($html, '//*[@id="collapseLastPatients"]//table');

    expect($row['Дата поступления'])->toBe(today()->subDays(3)->format('d.m.Y'))
        ->and($row['Дата рождения / возраст'])->toStartWith('09.03.1984 / ')
        ->and($row['Лечащий врач'])->toBe('Соколов Арсений Петрович')
        ->and(array_keys($row))->not->toContain('Палата')->not->toContain('Дата и название операции');
});

it('leads back to the department list of the open stay, not to the previous page', function (): void {
    // The button was url()->previous(): after any order or prescription posted from the card, the
    // browser's referer is the card itself, so "Вернуться в отделение" reloaded the card.
    $stay = ExaminationMap::factory()->create();
    $card = route('patients.show', $stay->patient_id);
    $outpatient = Patient::factory()->create();

    actingAs(adminWithPermissions('view_patients', 'list_patients'), 'admin');

    expect(cardBackLink($this->get($card, ['referer' => $card])->assertOk(), 'Вернуться в отделение'))
        ->toBe(route('patients.index', ['department_id' => $stay->department_id]))
        ->and(cardBackLink($this->get(route('patients.show', $outpatient), ['referer' => $card]), 'К списку пациентов'))
        ->toBe(route('patients.index'));

    // Staff who may not open the list have no list to go back to.
    $withoutList = actingAs(adminWithPermissions('view_patients'), 'admin')->get($card, ['referer' => $card])->assertOk();

    expect(cardBackLink($withoutList, 'Вернуться в отделение'))->toBeNull()
        ->and(cardBackLink($withoutList, 'К списку пациентов'))->toBeNull();
});
