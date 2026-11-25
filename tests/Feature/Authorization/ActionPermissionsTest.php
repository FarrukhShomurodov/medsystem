<?php

use App\Enums\ServiceStatus;
use App\Enums\SupplyRequestStatus;
use App\Models\Admin;
use App\Models\Diagnosis;
use App\Models\ExaminationMap;
use App\Models\MainWarehouse;
use App\Models\Patient;
use App\Models\PatientDiagnosis;
use App\Models\PatientService;
use App\Models\SupplyRequest;
use App\Models\Warehouse;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\StaffPositionSeeder;
use Database\Seeders\WarehouseSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

/*
| Write actions that used to be open to every signed-in account, and the controls that lead to them.
*/

beforeEach(function (): void {
    seed([PermissionSeeder::class, StaffPositionSeeder::class, WarehouseSeeder::class]);
});

it('offers the permissions of these actions on the staff position form', function (): void {
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin')
        ->get(route('staffs.position.create'))
        ->assertOk()
        ->assertSee([
            'Приём оплаты в кассе',
            'Подтверждение услуг, приёмов и процедур пациента (регистратура)',
            'Просмотр остатков складов',
            'Приход и списание товаров общего склада',
            'Создание заявок на общий склад',
            'Одобрение заявок на общий склад',
        ]);
});

it('lets the cashier take payments and keeps the registrar from doing so', function (): void {
    $service = PatientService::factory()->confirmed()->create();
    $payment = ['records' => ["service:{$service->id}"]];

    actingAs(Admin::factory()->role('Регистратор')->create(), 'admin')
        ->put(route('cashier.updateAll'), $payment)
        ->assertForbidden();

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);

    actingAs(Admin::factory()->role('Кассир')->create(), 'admin')
        ->put(route('cashier.updateAll'), $payment)
        ->assertRedirect();

    expect($service->fresh()->status)->toBe(ServiceStatus::Paid);
});

it('shows the payment button of the cashier page to the cashier only', function (): void {
    actingAs(Admin::factory()->role('Кассир')->create(), 'admin')
        ->get(route('cashier.index'))
        ->assertOk()
        ->assertSee('Оплатить выбранные');

    actingAs(Admin::factory()->role('Регистратор')->create(), 'admin')
        ->get(route('cashier.index'))
        ->assertOk()
        ->assertDontSee('Оплатить выбранные');
});

it('lets the registrar confirm the services of a patient and keeps the cashier from doing so', function (): void {
    $service = PatientService::factory()->create(['status' => 'assigned']);

    actingAs(Admin::factory()->role('Кассир')->create(), 'admin')
        ->post(route('registrar.confirm', $service->patient_id))
        ->assertForbidden();

    expect($service->fresh()->status)->toBe(ServiceStatus::Assigned);

    actingAs(Admin::factory()->role('Регистратор')->create(), 'admin')
        ->post(route('registrar.confirm', $service->patient_id))
        ->assertRedirect();

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
});

it('adds a diagnosis from the patient card with create_patient_diagnoses only', function (): void {
    // The route named the permission without the "permission:" prefix, so it failed for everyone.
    $patient = Patient::factory()->create();
    $diagnosis = ['patient_id' => $patient->id, 'diagnosis_id' => Diagnosis::factory()->create()->id];

    actingAs(Admin::factory()->create()->givePermissionTo('view_patients'), 'admin')
        ->post(route('patient.diagnoses.store'), $diagnosis)
        ->assertForbidden();

    actingAs(Admin::factory()->create()->givePermissionTo('create_patient_diagnoses'), 'admin')
        ->post(route('patient.diagnoses.store'), $diagnosis)
        ->assertRedirectToRoute('patients.show', $patient);

    expect(PatientDiagnosis::query()->where('patient_id', $patient->id)->count())->toBe(1);
});

it('checks the permission on an examination map before its state', function (): void {
    // ExaminationMapIsActive redirects back from a closed map; only staff allowed to edit learn that.
    $closedMap = ExaminationMap::factory()->discharged()->create();

    actingAs(Admin::factory()->create()->givePermissionTo('view_patients'), 'admin')
        ->get(route('maps.edit', $closedMap))
        ->assertForbidden();

    actingAs(Admin::factory()->create()->givePermissionTo('edit_maps'), 'admin')
        ->get(route('maps.edit', $closedMap))
        ->assertRedirect()
        ->assertSessionHasErrors();
});

it('separates approving supply requests from viewing them', function (): void {
    $request = SupplyRequest::query()->create([
        'warehouse_id' => Warehouse::factory()->create()->id,
        'main_warehouse_id' => MainWarehouse::query()->firstOrFail()->id,
        'status' => 'pending',
    ]);
    $storekeeper = Admin::factory()->create()->givePermissionTo('main_warehouse_remain');

    actingAs($storekeeper, 'admin')
        ->get(route('supply-requests.index'))
        ->assertOk()
        ->assertDontSee('Принять заказ');

    actingAs($storekeeper, 'admin')
        ->post(route('supply-requests.approve', $request->id))
        ->assertForbidden();

    expect($request->fresh()->status)->toBe(SupplyRequestStatus::Pending);

    actingAs(Admin::factory()->create()->givePermissionTo(['main_warehouse_remain', 'approve_supply_requests']), 'admin')
        ->get(route('supply-requests.index'))
        ->assertOk()
        ->assertSee('Принять заказ');
});

it('separates receiving and writing off main warehouse stock from viewing it', function (): void {
    $viewer = Admin::factory()->create()->givePermissionTo('main_warehouse_remain');

    actingAs($viewer, 'admin')
        ->get(route('main.warehouse.remains'))
        ->assertOk()
        ->assertDontSee(route('main.warehouse.receipts.goods.index'));

    actingAs($viewer, 'admin')
        ->get(route('main.warehouse.receipts.goods.index'))
        ->assertForbidden();

    actingAs(Admin::factory()->create()->givePermissionTo(['main_warehouse_remain', 'edit_main_warehouse']), 'admin')
        ->get(route('main.warehouse.remains'))
        ->assertOk()
        ->assertSee(route('main.warehouse.receipts.goods.index'));
});
