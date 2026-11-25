<?php

use App\Enums\ServiceStatus;
use App\Models\Admin;
use App\Models\Document;
use App\Models\DocumentResponse;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\Patient;
use App\Models\PatientService;
use Database\Seeders\DocumentSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\WarehouseSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed([PermissionSeeder::class, WarehouseSeeder::class]);

    $this->admin = Admin::factory()->mainAdmin()->create();
});

it('no longer changes data on GET', function (string $uri): void {
    actingAs($this->admin, 'admin')->get($uri)->assertMethodNotAllowed();
})->with([
    'main warehouse write-off' => '/dashboard/main-warehouse/receipts-discard/medications/1',
    'medical document delete' => '/dashboard/document/response/answers/delete/1',
    'patient service status' => '/api/patient/service/update-status/1',
]);

it('writes main warehouse stock off with DELETE', function (): void {
    $batch = MainWarehouseMedication::factory()->create(['main_warehouse_id' => MainWarehouse::query()->firstOrFail()->id]);
    $url = route('main.warehouse.receipts.goods.discard', ['type' => 'medications', 'id' => $batch->id]);

    actingAs(Admin::factory()->role('Кассир')->create(), 'admin')->delete($url)->assertForbidden();

    actingAs($this->admin, 'admin')->delete($url)->assertRedirectToRoute('main.warehouse.remains');

    expect(MainWarehouseMedication::query()->whereKey($batch->id)->exists())->toBeFalse();
});

it('accepts only the known stock types for a write-off', function (): void {
    actingAs($this->admin, 'admin')
        ->delete('/dashboard/main-warehouse/receipts-discard/patients/1')
        ->assertNotFound();
});

it('deletes a medical document response with DELETE', function (): void {
    seed(DocumentSeeder::class);

    $response = DocumentResponse::query()->create([
        'patient_id' => Patient::factory()->create()->id,
        // The initial nursing assessment before the admission: the one sheet that may be deleted.
        'document_id' => Document::INITIAL_NURSING_ASSESSMENT,
        'total_score' => 0,
    ]);

    actingAs($this->admin, 'admin')
        ->from('/dashboard')
        ->delete(route('documents.show.answer.delete', $response))
        ->assertRedirect('/dashboard');

    expect($response->fresh())->toBeNull();
});

it('changes a patient service status with PATCH', function (): void {
    $service = PatientService::factory()->create();

    actingAs($this->admin, 'admin')
        ->patchJson(route('api.patient-services.update-status', $service), ['status' => 'confirmed'])
        ->assertOk();

    expect($service->fresh()->status)->toBe(ServiceStatus::Confirmed);
});
