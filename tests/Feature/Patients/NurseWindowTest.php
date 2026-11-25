<?php

use App\Models\Admin;
use App\Models\Department;
use App\Models\DocumentResponse;
use App\Models\DocumentScore;
use App\Models\ExaminationMap;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientDocumentScoreFinished;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Staff;
use App\Models\VitalParameter;
use Database\Seeders\DocumentSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Frontend\RenderedPage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/** A nurse's login, working in the given department, with access to the nurse window. */
function nurseOf(Department $department): Admin
{
    $nurse = Staff::factory()->nurse(['department_id' => $department->id])->create();

    return tap(adminWithPermissions('sister_window_show'))->update(['staff_id' => $nurse->id]);
}

beforeEach(function (): void {
    $this->ward = Department::factory()->create();
    $this->inWard = ExaminationMap::factory()->create(['department_id' => $this->ward->id]);
    $this->discharged = ExaminationMap::factory()->discharged()->create(['department_id' => $this->ward->id]);
    $this->elsewhere = ExaminationMap::factory()->create();
});

it('lists the inpatients of the nurse\'s department, without discharged patients', function (): void {
    // The list showed every patient who ever stayed in the department; opening a discharged one was a 500.
    $patients = actingAs(nurseOf($this->ward), 'admin')
        ->get(route('sister.window.index'))
        ->assertOk()
        ->viewData('patients');

    expect($patients->pluck('id')->all())->toBe([$this->inWard->patient_id]);
});

it('lists every inpatient for an account that does not work in a department', function (): void {
    $patients = actingAs(adminWithPermissions('sister_window_show'), 'admin')
        ->get(route('sister.window.index'))
        ->viewData('patients');

    expect($patients->pluck('id')->sort()->values()->all())
        ->toBe(collect([$this->inWard->patient_id, $this->elsewhere->patient_id])->sort()->values()->all());
});

it('opens the vital signs of an inpatient of the nurse\'s department', function (): void {
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);
    $this->inWard->vitalParameters()->attach($pulse->id, ['value' => 76, 'created_at' => now(), 'updated_at' => now()]);

    $response = actingAs(nurseOf($this->ward), 'admin')
        ->get(route('sister.window.show', $this->inWard->patient_id))
        ->assertOk();

    expect($response->viewData('examinationMap')->id)->toBe($this->inWard->id)
        ->and($response->viewData('records')->first()->pulse)->toBe(76.0);
});

it('sends the nurse back to the list for a patient without an open stay in the department', function (string $map): void {
    // The page dereferenced the missing active map: a 500.
    actingAs(nurseOf($this->ward), 'admin')
        ->get(route('sister.window.show', $this->{$map}->patient_id))
        ->assertRedirectToRoute('sister.window.index')
        ->assertSessionHas('warning');
})->with(['discharged', 'elsewhere']);

it('opens the nurse window for a staff member without professional data', function (): void {
    $employee = Staff::factory()->create();
    $account = tap(adminWithPermissions('sister_window_show'))->update(['staff_id' => $employee->id]);

    actingAs($account, 'admin')->get(route('sister.window.index'))->assertOk();
});

it('records a measurement on the open stay', function (): void {
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);
    $temperature = VitalParameter::query()->create(['name' => 'Температура']);

    actingAs(nurseOf($this->ward), 'admin')
        ->post(route('patient.vital.parameters.store', $this->inWard->patient_id), [
            'parameters' => [$pulse->id => '80', $temperature->id => '36.6'],
        ])
        ->assertSessionHas('success');

    expect($this->inWard->vitalParameters()->pluck('value', 'vital_parameter_id')->map(fn ($value): float => (float) $value)->all())
        ->toEqual([$pulse->id => 80.0, $temperature->id => 36.6]);
});

it('refuses a measurement for a patient without an open stay', function (): void {
    // The active map was dereferenced without a check: a 500.
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);

    actingAs(nurseOf($this->ward), 'admin')
        ->post(route('patient.vital.parameters.store', $this->discharged->patient_id), ['parameters' => [$pulse->id => '80']])
        ->assertSessionHas('error');

    expect($this->discharged->vitalParameters()->count())->toBe(0);
});

it('rejects values that are not numbers and parameters that do not exist', function (): void {
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);
    $nurse = nurseOf($this->ward);

    actingAs($nurse, 'admin')
        ->post(route('patient.vital.parameters.store', $this->inWard->patient_id), ['parameters' => [$pulse->id => 'высокий']])
        ->assertSessionHasErrors("parameters.{$pulse->id}");

    actingAs($nurse, 'admin')
        ->post(route('patient.vital.parameters.store', $this->inWard->patient_id), ['parameters' => [999999 => '80']])
        ->assertSessionHasErrors('parameters');

    actingAs($nurse, 'admin')
        ->post(route('patient.vital.parameters.store', $this->inWard->patient_id), ['parameters' => [$pulse->id => '']])
        ->assertSessionHasErrors('parameters');

    expect($this->inWard->vitalParameters()->count())->toBe(0);
});

it('rejects a parameter id too large for the database instead of failing on it', function (): void {
    // The id reached "where id in (...)", and PostgreSQL answers an out-of-range bigint with an error: a 500.
    actingAs(nurseOf($this->ward), 'admin')
        ->post(route('patient.vital.parameters.store', $this->inWard->patient_id), ['parameters' => ['99999999999999999999' => '80']])
        ->assertRedirect()
        ->assertSessionHasErrors('parameters');
});

it('saves the normal values of a patient, replacing the previous ones', function (): void {
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);
    $temperature = VitalParameter::query()->create(['name' => 'Температура']);
    $patient = Patient::factory()->create();
    $patient->vitalNormalParameters()->attach($pulse->id, ['normal' => 60]);

    actingAs(adminWithPermissions('edit_patients'), 'admin')
        ->post(route('patient.normal.vital.parameter.store', $patient), [
            'parameters' => [$pulse->id => '72', $temperature->id => '36.6'],
        ])
        ->assertSessionHas('success');

    expect($patient->vitalNormalParameters()->pluck('normal', 'vital_parameters.id')->map(fn ($value): float => (float) $value)->all())
        ->toEqual([$pulse->id => 72.0, $temperature->id => 36.6]);
});

it('forbids the normal values to staff who may not edit patients', function (): void {
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('view_patients', 'sister_window_show'), 'admin')
        ->post(route('patient.normal.vital.parameter.store', $patient), ['parameters' => [$pulse->id => '72']])
        ->assertForbidden();

    expect($patient->vitalNormalParameters()->count())->toBe(0);
});

it('refuses a measurement for an inpatient of another department', function (): void {
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);

    actingAs(nurseOf($this->ward), 'admin')
        ->post(route('patient.vital.parameters.store', $this->elsewhere->patient_id), ['parameters' => [$pulse->id => '80']])
        ->assertSessionHas('error');

    expect($this->elsewhere->vitalParameters()->count())->toBe(0);
});

it('rejects a value sent as a list instead of failing on it', function (): void {
    // The after() hook cast every value to a string, an array included: "Array to string conversion", a 500.
    $pulse = VitalParameter::query()->create(['name' => 'Пульс']);

    actingAs(nurseOf($this->ward), 'admin')
        ->post(route('patient.vital.parameters.store', $this->inWard->patient_id), ['parameters' => [$pulse->id => ['80']]])
        ->assertRedirect()
        ->assertSessionHasErrors("parameters.{$pulse->id}");

    expect($this->inWard->vitalParameters()->count())->toBe(0);
});

it('shows the orders of the open stay in the treatment plan instead of made-up ones', function (): void {
    // The procedures and research tabs and the notifications above the page were static template
    // rows ("Инъекция цефтриаксона", "Рентген легких") shown for every patient, and a style block
    // outside the sections was sent before the doctype (quirks mode).
    $patient = $this->inWard->patient;
    $procedure = PatientProcedure::factory()->for($patient)->create(['examination_map_id' => $this->inWard->id]);
    $service = PatientService::factory()->for($patient)->create(['examination_map_id' => $this->inWard->id]);
    $prescription = PatientMedication::factory()->for($patient)->create(['examination_map_id' => $this->inWard->id]);
    $earlierStay = ExaminationMap::factory()->discharged()->for($patient)->create();
    $earlierPrescription = PatientMedication::factory()->for($patient)->create([
        'examination_map_id' => $earlierStay->id,
        'medication_id' => Medication::factory()->create(['name' => 'Препарат прошлой госпитализации'])->id,
    ]);

    $response = actingAs(nurseOf($this->ward), 'admin')
        ->get(route('sister.window.show', $patient))
        ->assertOk()
        ->assertSee([$procedure->procedure->name, $service->service->name, $prescription->medication->name])
        ->assertDontSee([$earlierPrescription->medication->name, 'Инъекция цефтриаксона', 'Рентген легких', 'Новое: ЭКГ в 10:00']);

    expect(ltrim($response->getContent()))->toStartWith('<!DOCTYPE html>');
});

it('loads the treatment plan with the same number of queries however many orders the stay has', function (): void {
    $nurse = nurseOf($this->ward);
    $queries = function (int $orders) use ($nurse): int {
        $map = ExaminationMap::factory()->create(['department_id' => $this->ward->id]);
        PatientProcedure::factory()->count($orders)->for($map->patient)->create(['examination_map_id' => $map->id]);
        PatientService::factory()->count($orders)->for($map->patient)->create(['examination_map_id' => $map->id]);
        PatientMedication::factory()->count($orders)->for($map->patient)->create(['examination_map_id' => $map->id]);
        DB::flushQueryLog();
        DB::enableQueryLog();
        actingAs($nurse, 'admin')->get(route('sister.window.show', $map->patient_id))->assertOk();

        return count(DB::getQueryLog());
    };

    $queries(1);

    expect($queries(4))->toBe($queries(1));
});

it('lists only what the system records of an inpatient, with the age in correct Russian', function (): void {
    // "№ палаты", "Вес", "Рост и ИМТ" and "Аллергия" were a dash for every patient: nothing records
    // them, and a dash under "Аллергия" reads as "no known allergies". The age was "62 лет".
    $this->inWard->patient->update(['birth_date' => today()->subYears(62)->subDay()->toDateString()]);

    $html = (string) actingAs(nurseOf($this->ward), 'admin')
        ->get(route('sister.window.index'))
        ->assertOk()
        ->getContent();
    [$row] = RenderedPage::tableRows($html, '//main//table');

    expect(array_keys($row))->toBe(['ФИО / ID', 'Возраст', 'Дней в стационаре'])
        ->and($row['Возраст'])->toBe('62 года');
});

describe('the recommended actions of a scored sheet', function (): void {
    // The score button's two endpoints took any response id: a nurse read the risk band of another
    // department's patients and of closed stays, and marked its actions done.
    beforeEach(function (): void {
        seed(DocumentSeeder::class);
    });

    /** A Morse sheet in the high-risk range (from 51) of the stay's patient, on the stay or on none. */
    function highRiskMorseSheet(ExaminationMap $stay, bool $onTheStay = true): DocumentResponse
    {
        $sheet = DocumentResponse::factory()->state(['document_id' => 2, 'total_score' => 60]);

        return ($onTheStay ? $sheet->onStay($stay) : $sheet->for($stay->patient))->create();
    }

    /**
     * The first action recommended for the sheet's total, as the score button marks it done.
     *
     * @return array{document_response_id: int, document_score_id: int, document_action_id: int}
     */
    function firstActionOf(DocumentResponse $sheet): array
    {
        $range = DocumentScore::query()->where('document_id', $sheet->document_id)->containing($sheet->total_score)->firstOrFail();

        return ['document_response_id' => $sheet->id, 'document_score_id' => $range->id, 'document_action_id' => $range->actions()->firstOrFail()->id];
    }

    it('opens and marks them on a sheet of an open stay in the nurse\'s department', function (): void {
        $sheet = highRiskMorseSheet($this->inWard);
        actingAs(nurseOf($this->ward), 'admin');

        $this->getJson(route('api.document-scores.actions', [2, $sheet]))->assertOk()->assertJsonPath('from_score', 51);
        $this->postJson(route('api.document-actions.finish'), firstActionOf($sheet))->assertOk();

        expect($sheet->finishedScore()->count())->toBe(1);
    });

    it('refuses them on a sheet the nurse window does not show', function (string $map, bool $onTheStay): void {
        $sheet = highRiskMorseSheet($this->{$map}, $onTheStay);
        actingAs(nurseOf($this->ward), 'admin');

        $this->getJson(route('api.document-scores.actions', [2, $sheet]))
            ->assertForbidden()
            ->assertJsonPath('message', 'Рекомендации открываются только по документам открытой госпитализации вашего отделения.');
        $this->postJson(route('api.document-actions.finish'), firstActionOf($sheet))->assertForbidden();

        expect(PatientDocumentScoreFinished::query()->count())->toBe(0);
    })->with([
        'a stay of another department' => ['elsewhere', true],
        'a closed stay of the department' => ['discharged', true],
        'a sheet an inpatient of the department had filled in before the admission' => ['inWard', false],
    ]);

    it('opens them on every department\'s open stays to an account that works in none, never on a closed one', function (): void {
        actingAs(adminWithPermissions('sister_window_show'), 'admin');

        $this->getJson(route('api.document-scores.actions', [2, highRiskMorseSheet($this->elsewhere)]))->assertOk();
        $this->getJson(route('api.document-scores.actions', [2, highRiskMorseSheet($this->discharged)]))->assertForbidden();
    });
});
