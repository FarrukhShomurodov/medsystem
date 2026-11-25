<?php

use App\Enums\ReceptionStatus;
use App\Models\Admin;
use App\Models\AdminSelectedDiagnosis;
use App\Models\Diagnosis;
use App\Models\DocumentResponse;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Reception;
use App\Models\ServiceType;
use App\Models\Staff;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\StaffPositionSeeder;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as Router;
use Tests\Feature\Scheduling\SchedulingFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/**
 * @return Collection<int, Route>
 */
function dashboardAjaxRoutes(): Collection
{
    return collect(Router::getRoutes()->getRoutes())
        ->filter(fn (Route $route): bool => str_starts_with($route->uri(), 'api/'))
        ->values();
}

beforeEach(function (): void {
    seed([PermissionSeeder::class, StaffPositionSeeder::class, ServiceTypeSeeder::class]);
});

it('runs every /api route on the admin session and behind a permission', function (): void {
    $routes = dashboardAjaxRoutes();

    expect($routes)->not->toBeEmpty();

    foreach ($routes as $route) {
        $middleware = $route->gatherMiddleware();

        expect($middleware)->toContain('web', 'auth:admin')
            ->and(collect($middleware)->contains(fn (string $name): bool => str_starts_with($name, 'permission:')))
            ->toBeTrue("{$route->uri()} has no permission middleware");
    }
});

it('puts every /api route naming an employee or an appointment behind ReceptionPolicy', function (): void {
    // A doctor's account works only on its own calendar; a new endpoint without the policy would let
    // it read or change a colleague's again.
    $routes = dashboardAjaxRoutes()
        ->filter(fn (Route $route): bool => array_intersect($route->parameterNames(), ['staff', 'reception']) !== []);

    expect($routes)->not->toBeEmpty();

    foreach ($routes as $route) {
        $check = in_array('reception', $route->parameterNames(), true)
            ? 'can:workOnCalendar,reception'
            : 'can:workOnCalendar,'.Reception::class.',staff';

        expect(in_array($check, $route->gatherMiddleware(), true))->toBeTrue("{$route->uri()} does not ask ReceptionPolicy");
    }
});

it('answers every /api route with a JSON 401 for guests', function (): void {
    foreach (dashboardAjaxRoutes() as $route) {
        $uri = '/'.preg_replace('/\{\w+\}/', '1', $route->uri());

        foreach (array_diff($route->methods(), ['HEAD']) as $method) {
            // No Accept header: the views call some endpoints with a plain fetch().
            $response = $this->call($method, $uri);

            expect($response->status())->toBe(401, "{$method} {$uri}")
                ->and($response->json('message'))->toBe('Unauthenticated.');
        }
    }
});

dataset('ajax endpoints with the permission of their page', [
    'patient picker of the calendars' => ['view_appointment_receptions', fn (): array => ['GET', '/api/patients']],
    'patient picker of the patient service form (create)' => ['create_patient_services', fn (): array => ['GET', '/api/patients']],
    'patient picker of the patient service form (edit)' => ['edit_patient_services', fn (): array => ['GET', '/api/patients']],
    'appointment details on the doctor calendar' => [
        'view_reception_doctor_calendar',
        fn (): array => ['GET', '/api/receptions/'.Reception::factory()->create()->id],
    ],
    'booking an appointment' => ['create_receptions', fn (): array => ['POST', '/api/receptions', bookingPayload()]],
    'cancelling an appointment' => [
        'change_receptions_status',
        fn (): array => ['PUT', '/api/reception-status-update/'.Reception::factory()->create()->id, ['status' => 'cancelled']],
    ],
    'history card of the patient page' => [
        'view_patients',
        fn (): array => ['POST', '/api/admin/patient/history/partial', ['type' => 'reception', 'id' => Reception::factory()->create()->id]],
    ],
    'service status on the patient page' => [
        'edit_patient_services',
        fn (): array => ['PATCH', '/api/patient/service/update-status/'.PatientService::factory()->create()->id, ['status' => 'confirmed']],
    ],
    'services of a type in the patient service modal' => [
        'create_patient_services',
        fn (): array => ['GET', '/api/service/by-type/'.ServiceType::query()->firstOrFail()->id],
    ],
    // The nurse window shows the sheets of open stays only (DocumentResponsePolicy::trackActions()).
    'recommended actions in the nurse window' => [
        'sister_window_show',
        fn (): array => ['GET', (fn (DocumentResponse $response): string => "/api/document-scores/{$response->document_id}/{$response->id}")(DocumentResponse::factory()->onStay(ExaminationMap::factory()->create())->create())],
    ],
]);

it('requires the permission of the calling page', function (string $permission, array $request): void {
    [$method, $uri, $data] = $request + [2 => []];
    $admin = Admin::factory()->create();

    actingAs($admin, 'admin')->json($method, $uri, $data)->assertForbidden();

    $admin->givePermissionTo($permission);

    actingAs($admin->fresh(), 'admin')->json($method, $uri, $data)->assertSuccessful();
})->with('ajax endpoints with the permission of their page');

it('keeps the appointment details to the calendars when the patient picker serves the order form too', function (): void {
    $reception = Reception::factory()->create();

    actingAs(adminWithPermissions('create_patient_services', 'edit_patient_services'), 'admin')
        ->getJson("/api/receptions/{$reception->id}")
        ->assertForbidden();
});

it('lets staff with change_receptions_status cancel an appointment', function (): void {
    // Regression: the endpoint used to run without a session, so the permission check always failed.
    $reception = Reception::factory()->create();
    $registrar = Admin::factory()->create()->givePermissionTo('change_receptions_status');

    actingAs($registrar, 'admin')
        ->putJson("/api/reception-status-update/{$reception->id}", ['status' => 'cancelled'])
        ->assertOk();

    expect($reception->fresh()->status)->toBe(ReceptionStatus::Cancelled);
});

it('answers invalid input on an /api route with a JSON 422', function (): void {
    $reception = Reception::factory()->create();

    actingAs(Admin::factory()->mainAdmin()->create(), 'admin')
        ->call('PUT', "/api/reception-status-update/{$reception->id}", ['status' => 'archived'])
        ->assertUnprocessable()
        // Rule::enum had no Russian message, so the user read the raw key "validation.enum".
        ->assertJsonValidationErrors(['status' => 'Выбранное значение для статус ошибочно.']);

    expect($reception->fresh()->status)->toBe(ReceptionStatus::New);
});

it('shows a calendar account only the appointment, never the patient card or the stay', function (string $calendar): void {
    // The endpoint sent $reception->toArray() with the patient, the stay and the doctor loaded:
    // passport, phones, insurance, diagnoses and complaints, the doctor's signature image, to
    // accounts that may not open the patient card (view_patients).
    $patient = Patient::factory()->create(['passport_series' => 'AB7654321', 'insurance_policy_number' => 'POL-555']);
    $map = ExaminationMap::factory()->for($patient)->create(['complaints' => 'Боль в груди', 'admission_diagnosis_code' => 'I20.0']);
    $doctor = Staff::factory()->doctor()->create(['sign_url' => 'staff/signs/doctor.png']);
    $reception = Reception::factory()->for($patient)->for($doctor)->create(['examination_map_id' => $map->id]);
    $viewer = adminWithPermissions($calendar);

    expect($viewer->can('view_patients'))->toBeFalse();

    actingAs($viewer, 'admin')
        ->getJson("/api/receptions/{$reception->id}")
        ->assertOk()
        ->assertJsonPath('patient_name', $patient->full_name)
        ->assertJsonPath('staff_name', $doctor->full_name)
        ->assertJsonMissingPath('patient')
        ->assertJsonMissingPath('map')
        ->assertJsonMissingPath('staff')
        ->assertJsonMissingPath('price')
        ->assertDontSee(['AB7654321', 'POL-555', 'Боль в груди', 'I20.0', 'signs/doctor.png', 'passport_series']);
})->with(['view_appointment_receptions', 'view_reception_doctor_calendar']);

it('toggles a favourite diagnosis of the signed-in admin only', function (): void {
    // Regression: admin_id used to come from the request body.
    $diagnosis = Diagnosis::factory()->create();
    $doctor = Admin::factory()->create()->givePermissionTo('create_patient_diagnoses');
    $colleague = Admin::factory()->create();

    actingAs($doctor, 'admin')
        ->postJson('/api/selected-diagnosis', ['diagnosis_id' => $diagnosis->id, 'admin_id' => $colleague->id])
        ->assertOk();

    expect(AdminSelectedDiagnosis::query()->where('diagnosis_id', $diagnosis->id)->pluck('admin_id')->all())
        ->toBe([$doctor->id]);

    actingAs($doctor, 'admin')
        ->postJson('/api/selected-diagnosis', ['diagnosis_id' => $diagnosis->id])
        ->assertOk();

    expect(AdminSelectedDiagnosis::query()->count())->toBe(0);
});

/**
 * A slot the booking modal offers: a doctor with reception hours today and an appointment service.
 *
 * @return array<string, mixed>
 */
function bookingPayload(): array
{
    return SchedulingFixtures::booking(SchedulingFixtures::doctor(), '10:00', '10:20');
}
