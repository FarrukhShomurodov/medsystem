<?php

use App\Models\Admin;
use App\Models\MainWarehouseMedication;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\SupplyRequest;
use App\Services\Clinical\PatientMedicationService;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

/** The data migration that rewrote the class names stored before the morph map. */
function morphAliasesMigration(): object
{
    return require database_path('migrations/2025_11_20_110100_store_morph_aliases_in_polymorphic_columns.php');
}

it('stores the alias of the model in every polymorphic column, not its class name', function (): void {
    $patient = Patient::factory()->create();
    $file = $patient->files()->create(['name' => 'Выписка.pdf', 'url' => 'patient_files/discharge.pdf']);

    $medication = Medication::factory()->create();
    $batch = StockFixtures::mainBatch($medication, 10, StockFixtures::monthsAhead(6));
    $prescription = app(PatientMedicationService::class)->sendToFloor(
        PatientMedication::factory()->for($patient)->create(['medication_id' => $medication->id, 'dosage' => 2]),
    );

    $admin = Admin::factory()->create()->assignRole(Role::findOrCreate('Медсестра', 'admin'));

    expect(DB::table('files')->where('id', $file->id)->value('fileable_type'))->toBe('patient')
        ->and(DB::table('stock_movements')->where('source_id', $prescription->id)->sole(['stock_type', 'item_type', 'source_type']))
        ->toEqual((object) ['stock_type' => 'main_warehouse_medication', 'item_type' => 'medication', 'source_type' => 'patient_medication'])
        ->and(DB::table('model_has_roles')->where('model_id', $admin->id)->value('model_type'))->toBe('admin')
        ->and(StockMovement::query()->sole()->stock)->toBeInstanceOf(MainWarehouseMedication::class)
        ->and($batch->fresh()->quantity)->toBe(8);
});

it('refuses to store a model that has no alias', function (): void {
    expect(fn () => (new Service)->getMorphClass())->toThrow(ClassMorphViolationException::class);
});

it('rewrites stored class names to aliases, once, and back on rollback', function (): void {
    $patient = Patient::factory()->create();
    $admin = Admin::factory()->create()->assignRole(Role::findOrCreate('Регистратор', 'admin'));
    $request = SupplyRequest::factory()->create();
    $medication = Medication::factory()->create();
    DB::table('files')->insert(['name' => 'Анализ.pdf', 'url' => 'patient_files/a.pdf', 'fileable_type' => 'App\Models\Patient', 'fileable_id' => $patient->id]);
    DB::table('supply_request_items')->insert(['request_id' => $request->id, 'supplyable_type' => 'App\Models\Medication', 'supplyable_id' => $medication->id, 'requested_quantity' => 3]);
    DB::table('model_has_roles')->update(['model_type' => 'App\Models\Admin']);

    $migration = morphAliasesMigration();
    $migration->up();
    $migration->up();

    expect(DB::table('files')->value('fileable_type'))->toBe('patient')
        ->and(DB::table('supply_request_items')->value('supplyable_type'))->toBe('medication')
        ->and(DB::table('model_has_roles')->pluck('model_type')->all())->toBe(['admin'])
        ->and($admin->fresh()->hasRole('Регистратор'))->toBeTrue()
        ->and($request->items()->sole()->supplyable)->toBeInstanceOf(Medication::class);

    $migration->down();

    expect(DB::table('files')->value('fileable_type'))->toBe('App\Models\Patient')
        ->and(DB::table('supply_request_items')->value('supplyable_type'))->toBe('App\Models\Medication')
        ->and(DB::table('model_has_roles')->value('model_type'))->toBe('App\Models\Admin');
});

it('keeps one role grant when the class name and the alias were both stored for it', function (): void {
    $admin = Admin::factory()->create()->assignRole($role = Role::findOrCreate('Кассир', 'admin'));
    DB::table('model_has_roles')->insert(['role_id' => $role->id, 'model_id' => $admin->id, 'model_type' => 'App\Models\Admin']);

    morphAliasesMigration()->up();

    expect(DB::table('model_has_roles')->get(['role_id', 'model_id', 'model_type'])->all())
        ->toEqual([(object) ['role_id' => $role->id, 'model_id' => $admin->id, 'model_type' => 'admin']]);
});
