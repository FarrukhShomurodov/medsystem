<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ServicePlace;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentResponse;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientService;
use App\Models\Specialty;
use App\Models\Staff;
use Database\Seeders\StaffPositionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/**
 * The admission form of a patient.
 *
 * @param  array<string, mixed>  $fields
 * @return array<string, mixed>
 */
function admissionForm(Patient $patient, array $fields = []): array
{
    return [
        'patient_id' => $patient->id,
        'staff_id' => Staff::factory()->doctor()->create()->id,
        'department_id' => Department::factory()->create()->id,
        'admission_date' => today()->toDateString(),
        'admission_diagnosis_code' => 'J18',
        ...$fields,
    ];
}

it('opens a stay for the patient and returns to the card', function (): void {
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->post(route('maps.store'), admissionForm($patient))
        ->assertRedirectToRoute('patients.show', $patient)
        ->assertSessionHas('success');

    expect($patient->activeExaminationMap()->exists())->toBeTrue();
});

it('refuses a second open stay, also when the form is posted twice', function (): void {
    // Only the GET form was guarded, so a double submit of the form opened two active maps.
    $patient = Patient::factory()->create();
    $doctor = actingAs(adminWithPermissions('create_patient_map'), 'admin');

    $doctor->post(route('maps.store'), admissionForm($patient))->assertSessionHas('success');
    $doctor->post(route('maps.store'), admissionForm($patient))
        ->assertRedirectToRoute('patients.show', $patient)
        ->assertSessionHas('error');

    expect($patient->examinationMaps()->count())->toBe(1);
});

it('records an already finished stay next to an open one', function (): void {
    $patient = Patient::factory()->create();
    ExaminationMap::factory()->for($patient)->create();

    actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->post(route('maps.store'), admissionForm($patient, [
            'admission_date' => now()->subMonth()->toDateString(),
            'discharge_date' => now()->subWeeks(3)->toDateString(),
        ]))
        ->assertSessionHas('success');

    expect($patient->examinationMaps()->where('is_active', false)->count())->toBe(1);
});

it('keeps two open stays of one patient out of the database', function (): void {
    $patient = Patient::factory()->create();
    ExaminationMap::factory()->for($patient)->create();

    // A failed statement aborts the surrounding test transaction in PostgreSQL: run it in a savepoint.
    DB::beginTransaction();
    expect(fn () => ExaminationMap::factory()->for($patient)->create())->toThrow(QueryException::class);
    DB::rollBack();

    expect($patient->examinationMaps()->count())->toBe(1);
});

it('accepts only an existing department, doctor and patient', function (): void {
    // A typed-in department was saved as an App\Models\Institution, a class that does not exist.
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->post(route('maps.store'), admissionForm($patient, ['department_id' => 'Новое отделение']))
        ->assertSessionHasErrors('department_id');

    actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->post(route('maps.store'), admissionForm($patient, ['department_id' => 999999, 'staff_id' => 999999]))
        ->assertSessionHasErrors(['department_id', 'staff_id']);

    expect(ExaminationMap::query()->count())->toBe(0);
});

it('attaches the initial nursing assessment filled in before the admission', function (): void {
    $patient = Patient::factory()->create();
    $assessment = Document::factory()->create(['id' => Document::INITIAL_NURSING_ASSESSMENT]);
    $response = DocumentResponse::query()->create([
        'patient_id' => $patient->id, 'document_id' => $assessment->id, 'total_score' => 0,
    ]);

    actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->post(route('maps.store'), admissionForm($patient));

    expect($response->fresh()->examination_map_id)->toBe($patient->activeExaminationMap->id);
});

it('forbids opening a stay without create_patient_map', function (): void {
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('edit_maps'), 'admin')
        ->post(route('maps.store'), admissionForm($patient))
        ->assertForbidden();

    expect(ExaminationMap::query()->count())->toBe(0);
});

it('closes the stay when a discharge date is saved and never moves it to another patient', function (): void {
    $map = ExaminationMap::factory()->create();
    $otherPatient = Patient::factory()->create();

    actingAs(adminWithPermissions('update_maps'), 'admin')
        ->put(route('maps.update', $map), [
            ...admissionForm($otherPatient),
            'admission_date' => $map->admission_date,
            'discharge_date' => today()->toDateString(),
        ])
        ->assertRedirectToRoute('patients.show', $map->patient_id);

    $map->refresh();

    expect($map->is_active)->toBeFalse()
        ->and($map->patient_id)->not->toBe($otherPatient->id);
});

it('lists the prescriptions of the stay on the edit form without inputs the form would ignore', function (): void {
    // The form rendered an editable prescription list that maps.update never saved: changes were
    // silently lost behind a success message.
    $map = ExaminationMap::factory()->create();
    $prescription = PatientMedication::factory()->for($map->patient)->create(['examination_map_id' => $map->id, 'dosage' => '2 таблетки']);
    $editLink = route('patient.medication.edit', $prescription);

    actingAs(adminWithPermissions('edit_maps'), 'admin')
        ->get(route('maps.edit', $map))
        ->assertOk()
        ->assertSee($prescription->medication->name)
        ->assertSee('2 таблетки')
        ->assertDontSeeHtml(['name="medications[', 'name="dosages['])
        ->assertDontSeeHtml($editLink);

    actingAs(adminWithPermissions('edit_maps', 'edit_patient_medication'), 'admin')
        ->get(route('maps.edit', $map))
        ->assertSeeHtml($editLink);
});

it('loads the edit form with the same number of queries however many prescriptions the stay has', function (): void {
    // The compatible medications of every prescription were loaded from Blade, one query per row.
    $viewer = adminWithPermissions('edit_maps');
    $queries = function (int $prescriptions) use ($viewer): int {
        $map = ExaminationMap::factory()->create();
        PatientMedication::factory()->count($prescriptions)->for($map->patient)->create(['examination_map_id' => $map->id]);
        DB::flushQueryLog();
        DB::enableQueryLog();
        actingAs($viewer, 'admin')->get(route('maps.edit', $map))->assertOk();

        return count(DB::getQueryLog());
    };

    $queries(1);

    expect($queries(4))->toBe($queries(1));
});

it('rejects a discharge before the admission', function (): void {
    $map = ExaminationMap::factory()->create(['admission_date' => '2025-11-10']);

    actingAs(adminWithPermissions('update_maps'), 'admin')
        ->put(route('maps.update', $map), [
            ...admissionForm($map->patient),
            'admission_date' => '2025-11-10',
            'discharge_date' => '2025-11-01',
        ])
        ->assertSessionHasErrors('discharge_date');

    expect($map->fresh()->is_active)->toBeTrue();
});

it('deletes an open stay and returns to the patient card', function (): void {
    // The redirect went to the undefined route maps.index: a 500 after the delete.
    $map = ExaminationMap::factory()->create();

    actingAs(adminWithPermissions('delete_maps'), 'admin')
        ->delete(route('maps.destroy', $map))
        ->assertRedirectToRoute('patients.show', $map->patient_id)
        ->assertSessionHas('success', 'Карта обследования успешно удалена!');

    expect(ExaminationMap::query()->whereKey($map->id)->exists())->toBeFalse();
});

it('keeps a closed stay: it is a medical record', function (): void {
    $map = ExaminationMap::factory()->discharged()->create();

    actingAs(adminWithPermissions('delete_maps'), 'admin')
        ->delete(route('maps.destroy', $map))
        ->assertRedirect()
        ->assertSessionHasErrors();

    expect(ExaminationMap::query()->whereKey($map->id)->exists())->toBeTrue();
});

it('checks the stay of the route, not a request field of the same name', function (): void {
    // ExaminationMapIsActive read $request->map, which prefers input: a blank "map" field skipped the check.
    $closed = ExaminationMap::factory()->discharged()->create();

    actingAs(adminWithPermissions('delete_maps'), 'admin')
        ->delete(route('maps.destroy', $closed), ['map' => ''])
        ->assertRedirect()
        ->assertSessionHasErrors();

    expect(ExaminationMap::query()->whereKey($closed->id)->exists())->toBeTrue();

    $open = ExaminationMap::factory()->create();

    actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->get(route('patient.map.create', $open->patient).'?patient=')
        ->assertRedirect()
        ->assertSessionHasErrors();
});

it('forbids deleting a stay without delete_maps', function (): void {
    $map = ExaminationMap::factory()->create();

    actingAs(adminWithPermissions('edit_maps', 'update_maps'), 'admin')
        ->delete(route('maps.destroy', $map))
        ->assertForbidden();
});

it('lists only the open stays in the stay picker of the service forms', function (): void {
    // Closed stays came back as null entries, which broke the picker's forEach.
    $patient = Patient::factory()->create();
    ExaminationMap::factory()->discharged()->for($patient)->create();
    $open = ExaminationMap::factory()->for($patient)->create();

    actingAs(adminWithPermissions('create_patient_services'), 'admin')
        ->getJson(route('api.examination-maps.by-patient', $patient))
        ->assertOk()
        ->assertExactJson([[
            'id' => $open->id,
            'patient_name' => $patient->full_name,
            'admission_date' => $open->admission_date,
            'department_name' => $open->department->name,
            'diagnosis' => $open->admission_diagnosis_description,
        ]]);
});

it('shows the speciality of the attending doctor on the map page', function (): void {
    // The page read professionalData->name, a column that does not exist.
    $cardiologist = Staff::factory()->doctor(['speciality_id' => Specialty::factory()->create(['name' => 'Кардиолог'])->id])->create();
    $map = ExaminationMap::factory()->create(['staff_id' => $cardiologist->id]);

    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('maps.show', $map))
        ->assertOk()
        ->assertSee($map->patient->last_name)
        ->assertSee('Кардиолог');
});

it('shows the place, status and noted payment of the services of the stay', function (): void {
    // The page echoed the payment and insurance arrays of a service, and failed with a TypeError.
    $map = ExaminationMap::factory()->create();
    PatientService::factory()->for($map->patient)->create([
        'examination_map_id' => $map->id,
        'place' => ServicePlace::Cabinet,
        'payment' => ['status' => PaymentStatus::PartiallyPaid->value, 'method' => PaymentMethod::InsuranceCompany->value],
        'insurance_policy' => ['company_name' => 'Узбекинвест'],
    ]);

    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('maps.show', $map))
        ->assertOk()
        ->assertSeeHtml('<span class="badge bg-success-lt me-1">Назначена</span>')
        ->assertSeeText('Кабинет')
        ->assertSeeText('Частично оплачено')
        ->assertSeeText('Узбекинвест');
});

it('leaves the attending doctor and the department of a new stay to be chosen', function (): void {
    // The disabled placeholders were not selected: a stay opened without touching them went to
    // the first doctor and the first department of the lists, past "required".
    $patient = Patient::factory()->create();
    Staff::factory()->doctor()->create();
    Department::factory()->create();

    $page = actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->get(route('patient.map.create', $patient))
        ->assertOk()
        ->assertSeeHtml('<option value="" disabled selected>Выберите врача</option>')
        ->assertSeeHtml('<option value="" disabled selected>Выберите отделение</option>');

    expect(RenderedPage::markupProblems((string) $page->getContent()))->toBe([]);

    $this->post(route('maps.store'), admissionForm($patient, ['staff_id' => null, 'department_id' => null]))
        ->assertSessionHasErrors(['staff_id', 'department_id']);
});

it('keeps the attending doctor selected on the edit form when the doctor is off duty today', function (): void {
    // The select listed only today's doctors on duty: the browser picked another one, and saving the
    // form (to discharge the patient, say) silently reassigned the stay.
    $map = ExaminationMap::factory()->create();

    $response = actingAs(adminWithPermissions('edit_maps'), 'admin')
        ->get(route('maps.edit', $map))
        ->assertOk();

    expect($response->viewData('doctors')->pluck('id'))->toContain($map->staff_id);
    $response->assertSeeHtml('value="'.$map->staff_id.'" selected');
});

it('refuses a discharge date in the future: it would close the stay before the patient leaves', function (): void {
    // A saved discharge date closes the stay at once, and a closed map can no longer be edited.
    $map = ExaminationMap::factory()->create(['admission_date' => today()->subDays(3)->toDateString()]);

    actingAs(adminWithPermissions('update_maps'), 'admin')
        ->put(route('maps.update', $map), [
            ...admissionForm($map->patient),
            'admission_date' => $map->admission_date,
            'discharge_date' => today()->addDays(2)->toDateString(),
        ])
        ->assertSessionHasErrors('discharge_date');

    expect($map->fresh()->is_active)->toBeTrue();
});

it('offers to save the edit form only to staff who may update maps', function (): void {
    // The form needs edit_maps, saving it update_maps: the button answered with a 403.
    $map = ExaminationMap::factory()->create();

    actingAs(adminWithPermissions('edit_maps'), 'admin')
        ->get(route('maps.edit', $map))
        ->assertOk()
        ->assertDontSeeHtml('<button type="submit"');

    actingAs(adminWithPermissions('edit_maps', 'update_maps'), 'admin')
        ->get(route('maps.edit', $map))
        ->assertSeeHtml('<button type="submit"');
});

it('names the doctors of the stay form in full, by surname', function (): void {
    // The select listed bare first names: of two doctors called Арсений either could be saved, and
    // nothing on the screen showed which one.
    seed(StaffPositionSeeder::class);
    SchedulingFixtures::doctor()->update(['last_name' => 'Соколов', 'name' => 'Арсений', 'middle_name' => 'Петрович']);
    SchedulingFixtures::doctor()->update(['last_name' => 'Максимов', 'name' => 'Арсений', 'middle_name' => 'Иванович']);

    $page = (string) actingAs(adminWithPermissions('create_patient_map'), 'admin')
        ->get(route('patient.map.create', Patient::factory()->create()))
        ->assertOk()
        ->getContent();

    expect(RenderedPage::selectOptions($page, 'staff_id'))
        ->toBe(['Выберите врача', 'Максимов Арсений Иванович', 'Соколов Арсений Петрович']);
});
