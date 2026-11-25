<?php

use App\Models\ExaminationMap;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientAssignment;
use App\Models\PatientMedication;
use App\Models\PatientService;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    actingAs(adminWithPermissions('view_patients'), 'admin');
});

it('renders the detail of a history row', function (string $type, Closure $record): void {
    $this->postJson(route('api.patients.history.partial'), ['type' => $type, 'id' => $record()->getKey()])
        ->assertOk();
})->with([
    'the admission of a stay' => ['examination_map_start', fn () => ExaminationMap::factory()->create()],
    'the discharge of a stay' => ['examination_map_end', fn () => ExaminationMap::factory()->discharged()->create()],
    'a service' => ['service', fn () => PatientService::factory()->create()],
    // Medications without composition data crashed the partial.
    'a prescription' => ['medication', fn () => PatientMedication::factory()->create(['medication_id' => Medication::factory()->create()->id])],
]);

it('offers the edit link of an open stay only to staff who may edit maps', function (): void {
    $map = ExaminationMap::factory()->create();
    $editLink = route('maps.edit', $map);

    $this->postJson(route('api.patients.history.partial'), ['type' => 'examination_map', 'id' => $map->id])
        ->assertDontSeeHtml($editLink);

    actingAs(adminWithPermissions('view_patients', 'edit_maps'), 'admin')
        ->postJson(route('api.patients.history.partial'), ['type' => 'examination_map', 'id' => $map->id])
        ->assertSeeHtml($editLink);
});

it('answers 404 for a record that does not exist and 422 for an unknown type', function (): void {
    $this->postJson(route('api.patients.history.partial'), ['type' => 'service', 'id' => 999999])->assertNotFound();
    $this->postJson(route('api.patients.history.partial'), ['type' => 'patients', 'id' => 1])->assertUnprocessable();
});

it('offers the status select of a service only to staff who may edit services', function (): void {
    // Its route needs edit_patient_services: the select answered every change with a 403.
    $service = PatientService::factory()->create();
    $partial = fn () => $this->postJson(route('api.patients.history.partial'), ['type' => 'service', 'id' => $service->id]);

    $partial()->assertOk()->assertDontSeeHtml('id="service_status"');

    actingAs(adminWithPermissions('view_patients', 'edit_patient_services'), 'admin');

    $partial()->assertSeeHtml('id="service_status"');
});

it('offers only the statuses the order may move to next', function (): void {
    // The select listed every status, so "paid" or "assigned" could be picked for any order and the
    // server refused it.
    actingAs(adminWithPermissions('view_patients', 'edit_patient_services'), 'admin');
    $partial = fn (PatientService $service) => $this->postJson(route('api.patients.history.partial'), ['type' => 'service', 'id' => $service->id]);

    $assigned = $partial(PatientService::factory()->create(['status' => 'assigned']))->assertOk();
    foreach (['assigned', 'confirmed', 'cancelled'] as $offered) {
        $assigned->assertSeeHtml('<option value="'.$offered.'"');
    }
    foreach (['paid', 'in_process', 'completed'] as $refused) {
        $assigned->assertDontSeeHtml('<option value="'.$refused.'"');
    }

    $partial(PatientService::factory()->create(['status' => 'completed']))
        ->assertOk()
        ->assertDontSeeHtml('id="service_status"');

    // The endpoint refuses "paid" (payment is taken at the cash desk), so a confirmed order offers only the cancel.
    $confirmed = $partial(PatientService::factory()->create(['status' => 'confirmed']))->assertOk();
    $confirmed->assertSeeHtml('<option value="cancelled"')->assertDontSeeHtml('<option value="paid"');
});

it('asks before a service order is moved to a status it cannot leave', function (): void {
    // Picking "Отменена" in the select cancelled the order at once, and a cancelled order cannot be
    // restored from the screen.
    actingAs(adminWithPermissions('view_patients', 'edit_patient_services'), 'admin');
    $partial = fn (string $status): string => (string) $this->postJson(route('api.patients.history.partial'), [
        'type' => 'service',
        'id' => PatientService::factory()->create(['status' => $status])->id,
    ])->assertOk()->getContent();

    expect($partial('assigned'))
        ->toMatch('/<option value="cancelled"\s+data-question="Отменить заказ услуги «[^"]+»\? [^"]+"\s+data-accept="Отменить услугу"/u')
        ->not->toMatch('/<option value="confirmed"\s+data-question=/')
        ->and($partial('paid'))
        ->toMatch('/<option value="completed"\s+data-question="Завершить услугу «[^"]+»\? [^"]+"\s+data-accept="Завершить услугу"/u')
        ->not->toMatch('/<option value="in_process"\s+data-question=/');
});

it('offers to attach a document to a service only to staff who may fill in documents', function (): void {
    // The button opened a form whose route (response.survey.store) needs store_survey_response.
    $service = PatientService::factory()->create();
    $partial = fn () => $this->postJson(route('api.patients.history.partial'), ['type' => 'service', 'id' => $service->id]);

    $partial()->assertOk()->assertDontSeeHtml('data-bs-target="#createDocumentModal"');

    actingAs(adminWithPermissions('view_patients', 'store_survey_response'), 'admin');

    $partial()->assertSeeHtml('data-bs-target="#createDocumentModal"');
});

it('renders the initial of a Cyrillic staff name as a whole letter', function (): void {
    // substr() cut the first byte of a two-byte letter, which Blade escaped to the replacement character.
    $reception = Reception::factory()->create(['staff_id' => Staff::factory()->create(['name' => 'Шахноза'])->id]);

    $this->postJson(route('api.patients.history.partial'), ['type' => 'reception', 'id' => $reception->id])
        ->assertOk()
        ->assertDontSee("\u{FFFD}");
});

it('names the doctor of a stay, an appointment and a referral in full', function (string $type, Closure $record): void {
    // Only the first name was printed, so the same doctor was "Арсений" here and "Соколов Арсений
    // Петрович" on the dashboard.
    $doctor = Staff::factory()->create(['last_name' => 'Соколов', 'name' => 'Арсений', 'middle_name' => 'Петрович']);

    $this->postJson(route('api.patients.history.partial'), ['type' => $type, 'id' => $record($doctor)->getKey()])
        ->assertOk()
        ->assertSeeText('Соколов Арсений Петрович');
})->with([
    'a stay' => ['examination_map', fn (Staff $doctor) => ExaminationMap::factory()->create(['staff_id' => $doctor->id])],
    'an appointment' => ['reception', fn (Staff $doctor) => Reception::factory()->create(['staff_id' => $doctor->id])],
    'a referral' => ['assignment', fn (Staff $doctor) => PatientAssignment::query()->create([
        'patient_id' => Patient::factory()->create()->id,
        'service_id' => Service::factory()->create()->id,
        'staff_id' => $doctor->id,
    ])],
]);
