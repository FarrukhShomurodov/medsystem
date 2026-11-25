<?php

use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\MedicationComposition;
use App\Models\PatientMedication;
use App\Models\Pharmacy;
use App\Models\PharmacyMedication;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseAccounting;
use App\Models\WarehouseMedicalSupply;
use App\Services\MedicationService;
use App\Services\SupplierService;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
});

it('lists the medication batches of the department warehouses', function (): void {
    // Regression: the page eager-loaded relations the model did not have and answered 500.
    $warehouse = StockFixtures::departmentWarehouse();
    StockFixtures::departmentBatch($warehouse, Medication::factory()->create(['name' => 'Гепарин']), 5, StockFixtures::monthsAhead(3));
    StockFixtures::departmentBatch(StockFixtures::departmentWarehouse(), Medication::factory()->create(['name' => 'Аспирин']), 5, StockFixtures::monthsAhead(3));

    actingAs(adminWithPermissions('view_warehouse_medications'), 'admin');

    get(route('warehouses.medications', ['warehouse_id' => $warehouse->id]))
        ->assertOk()
        ->assertSeeText('Гепарин')
        ->assertDontSeeText('Аспирин');
});

it('opens a department batch without packaging or cost details', function (): void {
    $accounting = WarehouseAccounting::factory()->create();

    actingAs(adminWithPermissions('view_warehouse_medication_detail'), 'admin')
        ->get(route('warehouse.medication.show', $accounting))
        ->assertOk()
        ->assertSeeText($accounting->batch_number);
});

it('opens a warehouse without contact details and shows its e-mail as the e-mail', function (): void {
    $bare = Warehouse::factory()->create();
    $described = Warehouse::factory()->create();
    $described->contact()->create([
        'phone' => '+998 71 200-00-01',
        'email' => 'sklad@medsystem.test',
        'address' => 'Корпус 2',
        'work_time' => '08:00-17:00',
        'head_name' => 'Каримов А.',
        'head_position' => 'Заведующий складом',
        'head_contact' => '+998 90 000-00-00',
    ]);

    actingAs(adminWithPermissions('view_warehouses'), 'admin');

    get(route('warehouses.show', $bare))->assertOk();
    get(route('warehouses.show', $described))
        ->assertOk()
        ->assertSeeInOrder(['Email:', 'sklad@medsystem.test']);
});

it('lists the supplies of one department warehouse', function (): void {
    $warehouse = StockFixtures::departmentWarehouse();
    $other = StockFixtures::departmentWarehouse();
    $supply = MedicalSupply::factory()->create(['name' => 'Шприц 5 мл']);
    WarehouseMedicalSupply::query()->create(['warehouse_id' => $warehouse->id, 'supply_id' => $supply->id, 'quantity' => 40]);
    WarehouseMedicalSupply::query()->create(['warehouse_id' => $other->id, 'supply_id' => $supply->id, 'quantity' => 999]);

    actingAs(adminWithPermissions('view_warehouse_supplies', 'view_warehouse_remains'), 'admin');

    get(route('warehouses.supplies', ['warehouse_id' => $warehouse->id]))
        ->assertOk()
        ->assertSeeText('40')
        ->assertDontSeeText('999');

    get(route('warehouse.remains', ['warehouse_id' => $warehouse->id]))
        ->assertOk()
        ->assertSeeText('Шприц 5 мл')
        ->assertDontSeeText('999');
});

it('opens the pages of a medication without composition and other detail rows', function (): void {
    // The medication page, its edit form and the prescription edit form read composition->active_substance_name.
    $medication = Medication::factory()->create(['name' => 'Лоратадин']);
    $prescription = PatientMedication::factory()->create(['medication_id' => $medication->id]);

    actingAs(adminWithPermissions('view_medications', 'edit_medications', 'edit_patient_medication', 'view_patients'), 'admin');

    get(route('medications.show', $medication))->assertOk()->assertSeeText('Лоратадин');
    get(route('medications.edit', $medication))->assertOk();
    get(route('patient.medication.edit', $prescription))->assertOk()->assertSeeText('Лоратадин');
    getJson(route('api.medications.show', $medication))->assertOk()->assertJsonPath('composition.active_substance_name', null);
});

it('saves the edit form of a medication that had no detail rows', function (): void {
    $medication = Medication::factory()->create();

    actingAs(adminWithPermissions('edit_medications'), 'admin');

    put(route('medications.update', $medication), [
        ...$medication->only(['name', 'release_form', 'dosage', 'manufacturer_id', 'group_id', 'registration_number', 'min_deduction', 'min_quantity', 'notify_before_days', 'notify_min_qty']),
        'registration_date' => '2024-01-10',
        'active_substance_name' => 'Лоратадин',
        'active_substance_dosage' => '10 мг',
        'additional_substance' => [['substance_name' => 'Лактоза', 'substance_dosage' => '50 мг']],
        'administration_method' => 'Внутрь',
        'dosage_regimen' => '1 раз в день',
        'drug_interactions' => 'Нет данных',
        'storage_temperature' => 25,
        'storage_conditions' => 'Комнатная температура (15–25°C)',
    ])->assertRedirect(route('medications.index'))->assertSessionHas('success');

    $composition = MedicationComposition::query()->where('medication_id', $medication->id)->sole();

    expect($composition->active_substance_name)->toBe('Лоратадин')
        ->and($composition->additionalSubstance()->pluck('substance_name')->all())->toBe(['Лактоза'])
        ->and($medication->application()->value('dosage_regimen'))->toBe('1 раз в день');
});

it('names the excipient fields of the medication form in Russian when they are refused', function (): void {
    // The error read "Поле additional_substance.0.substance_dosage обязательно для заполнения."
    actingAs(adminWithPermissions('create_medications'), 'admin');

    post(route('medications.store'), [
        'additional_substance' => [['substance_name' => 'Лактоза', 'substance_dosage' => '']],
    ])->assertSessionHasErrors([
        'additional_substance.0.substance_dosage' => 'Поле дозировка вспомогательного вещества обязательно для заполнения.',
        'dosage' => 'Поле дозировка обязательно для заполнения.',
    ]);
});

it('lists the stock of a pharmacy', function (): void {
    $pharmacy = Pharmacy::factory()->create();
    PharmacyMedication::query()->create([
        'pharmacy_id' => $pharmacy->id,
        'medication_id' => Medication::factory()->create(['name' => 'Кларитромицин'])->id,
        'quantity' => 12,
    ]);

    actingAs(adminWithPermissions('list_pharmacies', 'view_pharmacy_medications'), 'admin');

    get(route('pharmacies.index'))->assertOk();
    get(route('pharmacy.medications', $pharmacy))->assertOk()->assertSeeText('Кларитромицин');
});

it('offers on the order form only what the main warehouse has in stock', function (): void {
    StockFixtures::mainBatch(Medication::factory()->create(['name' => 'Амоксициллин']), 10, StockFixtures::monthsAhead(2));
    StockFixtures::mainBatch(Medication::factory()->create(['name' => 'Закончился']), 0, StockFixtures::monthsAhead(2));

    actingAs(adminWithPermissions('create_supply_requests'), 'admin')
        ->get(route('warehouse.order'))
        ->assertOk()
        ->assertSee('Амоксициллин')
        ->assertDontSee('Закончился');
});

it('does not offer a drug whose only batch has expired', function (): void {
    $usable = Medication::factory()->create(['name' => 'Амброксол']);
    $expired = Medication::factory()->create(['name' => 'Просрочен']);
    StockFixtures::mainBatch($usable, 10, StockFixtures::monthsAhead(2));
    StockFixtures::mainBatch($expired, 10, today()->subDay()->toDateString());

    expect(Medication::query()->inMainWarehouseStock()->pluck('name')->all())->toBe(['Амброксол']);

    actingAs(adminWithPermissions('create_supply_requests'), 'admin')
        ->get(route('warehouse.order'))
        ->assertOk()
        ->assertSee('Амброксол')
        ->assertDontSee('Просрочен');
});

it('replaces a licence file of a medication only once its update is saved', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('medical_licenses/old.pdf', 'old');
    $medication = Medication::factory()->create();
    $medication->licenseAndCertification()->create(['sales_license' => 'medical_licenses/old.pdf']);
    $form = [
        ...$medication->only(['name', 'release_form', 'dosage', 'manufacturer_id', 'group_id', 'registration_number']),
        'active_substance_name' => 'Лоратадин',
        'active_substance_dosage' => '10 мг',
        'additional_substance' => [],
        'administration_method' => 'Внутрь',
        'dosage_regimen' => '1 раз в день',
        'drug_interactions' => 'Нет данных',
        'storage_temperature' => 25,
        'storage_conditions' => 'Комнатная температура (15–25°C)',
    ];
    $service = app(MedicationService::class);

    DB::beginTransaction();
    $service->update($medication, [...$form, 'sales_license' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf')]);
    DB::rollBack();

    Storage::disk('public')->assertExists('medical_licenses/old.pdf');

    $service->update($medication->fresh(), [...$form, 'sales_license' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf')]);

    Storage::disk('public')->assertMissing('medical_licenses/old.pdf');
    Storage::disk('public')->assertExists($medication->licenseAndCertification()->value('sales_license'));
});

it('refuses to delete a warehouse or a pharmacy that holds stock, and keeps the stock', function (): void {
    // The stock rows used to be cascaded away: units vanished without a write-off.
    $warehouse = StockFixtures::departmentWarehouse();
    $batch = StockFixtures::departmentBatch($warehouse, Medication::factory()->create(), 15, StockFixtures::monthsAhead(3));
    $pharmacy = Pharmacy::factory()->create();
    PharmacyMedication::query()->create(['pharmacy_id' => $pharmacy->id, 'medication_id' => $batch->medication_id, 'quantity' => 4]);

    actingAs(adminWithPermissions('list_warehouses', 'delete_warehouses', 'list_pharmacies', 'delete_pharmacies'), 'admin');

    // PostgreSQL aborts the test transaction after a refused DELETE; a savepoint keeps it usable.
    DB::beginTransaction();
    $this->from(route('warehouses.index'))->delete(route('warehouses.destroy', $warehouse))
        ->assertSessionHas('error', 'Запись нельзя удалить: она используется в других разделах.');
    DB::rollBack();

    DB::beginTransaction();
    $this->from(route('pharmacies.index'))->delete(route('pharmacies.destroy', $pharmacy))
        ->assertSessionHas('error', 'Запись нельзя удалить: она используется в других разделах.');
    DB::rollBack();

    expect($batch->fresh()?->quantity)->toBe(15)
        ->and(PharmacyMedication::query()->whereBelongsTo($pharmacy)->value('quantity'))->toBe(4);

    $empty = StockFixtures::departmentWarehouse();
    $this->delete(route('warehouses.destroy', $empty))->assertSessionHas('success');
    expect(Warehouse::query()->whereKey($empty->id)->exists())->toBeFalse();
});

it('replaces the contract of a supplier only once its update is saved', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('supplier_contract_terms/old.pdf', 'old');
    $supplier = Supplier::factory()->create(['contract_terms' => 'supplier_contract_terms/old.pdf']);
    $service = app(SupplierService::class);

    DB::beginTransaction();
    $service->update($supplier, ['contract_terms' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'), 'manufacturers' => []]);
    DB::rollBack();

    Storage::disk('public')->assertExists('supplier_contract_terms/old.pdf');

    $service->update($supplier->fresh(), ['contract_terms' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'), 'manufacturers' => []]);

    Storage::disk('public')->assertMissing('supplier_contract_terms/old.pdf');
    Storage::disk('public')->assertExists($supplier->fresh()->contract_terms);
});

describe('the filters of the medication list', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('list_medications'), 'admin');
    });

    it('narrows the list by group, manufacturer and active substance', function (): void {
        $wanted = Medication::factory()->create(['name' => 'Лоратадин']);
        MedicationComposition::query()->create(['medication_id' => $wanted->id, 'active_substance_name' => 'Лоратадин', 'active_substance_dosage' => '10 мг']);
        Medication::factory()->create(['name' => 'Цетиризин']);

        $listed = fn (array $filters): array => get(route('medications.index', $filters))->assertOk()
            ->viewData('medications')->getCollection()->pluck('name')->all();

        expect($listed(['group_id' => $wanted->group_id]))->toBe(['Лоратадин'])
            ->and($listed(['manufacturer_id' => $wanted->manufacturer_id]))->toBe(['Лоратадин'])
            ->and($listed(['active_substance_name' => 'Лоратадин']))->toBe(['Лоратадин']);
    });

    it('sends a filter that is not an id back to the unfiltered list, also when it is submitted twice', function (array $filters): void {
        // The raw value reached the bigint column: SQLSTATE 22P02 or 22003, a 500.
        $refused = route('medications.index', $filters);

        get($refused)->assertRedirect(route('medications.index'));
        get($refused)->assertRedirect(route('medications.index'))->assertSessionHasErrors(array_keys($filters));
    })->with([
        'a group that is not a number' => [['group_id' => 'abc']],
        'a group out of range' => [['group_id' => '99999999999999999999']],
        'a manufacturer with a suffix' => [['manufacturer_id' => '1x']],
        'a group that does not exist' => [['group_id' => '999999']],
    ]);
});
