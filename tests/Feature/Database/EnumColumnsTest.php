<?php

use App\Enums\AbsenceReason;
use App\Enums\AbsenceType;
use App\Enums\AdministrationRoute;
use App\Enums\AdmissionRule;
use App\Enums\EquipmentStatus;
use App\Enums\Gender;
use App\Enums\PackagingType;
use App\Enums\PatientMedicationStatus;
use App\Enums\QualificationCategory;
use App\Enums\QuestionType;
use App\Enums\ReceptionStatus;
use App\Enums\ScheduleType;
use App\Enums\ServicePlace;
use App\Enums\ServiceStatus;
use App\Enums\StockMovementReason;
use App\Enums\SupplyRequestStatus;
use App\Enums\TransferStatus;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\MainWarehouseMedication;
use App\Models\Manufacturer;
use App\Models\Staff;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\seed;

/**
 * The values the CHECK constraint of a column admits (Laravel enums are varchar + CHECK on PostgreSQL).
 *
 * @return list<string>
 */
function enumColumnValues(string $table, string $column): array
{
    $definition = DB::scalar('select pg_get_constraintdef(oid) from pg_constraint where conname = ?', ["{$table}_{$column}_check"]);
    preg_match_all("/'([^']*)'::/", (string) $definition, $values);

    return $values[1];
}

it('admits exactly the values of the enum the model casts the column to', function (string $table, string $column, string $enum): void {
    expect(enumColumnValues($table, $column))->toEqualCanonicalizing($enum::values());
})->with([
    ['patient_services', 'status', ServiceStatus::class],
    ['patient_procedures', 'status', ServiceStatus::class],
    ['patient_services', 'place', ServicePlace::class],
    ['receptions', 'status', ReceptionStatus::class],
    ['patient_medications', 'status', PatientMedicationStatus::class],
    ['patient_medications', 'method', AdministrationRoute::class],
    ['patient_medications', 'admission_rules', AdmissionRule::class],
    ['transfers', 'status', TransferStatus::class],
    ['supply_requests', 'status', SupplyRequestStatus::class],
    ['stock_movements', 'reason', StockMovementReason::class],
    ['patients', 'gender', Gender::class],
    ['staff', 'gender', Gender::class],
    ['staff_schedules', 'type', ScheduleType::class],
    ['staff_absences', 'type', AbsenceType::class],
    ['staff_absences', 'reason', AbsenceReason::class],
    ['staff_professional_data', 'category', QualificationCategory::class],
    ['survey_questions', 'type', QuestionType::class],
    ['document_questions', 'type', QuestionType::class],
    ['equipment', 'status', EquipmentStatus::class],
    ['medication_packagings', 'packaging_type', PackagingType::class],
    ['main_warehouse_medications', 'packaging_type', PackagingType::class],
]);

it('turns the Russian equipment and packaging values into the enum values and back', function (): void {
    seed(WarehouseSeeder::class);
    $equipment = Equipment::query()->create([
        'name' => 'Аппарат УЗИ',
        'equipment_type_id' => EquipmentType::query()->create(['name' => 'Диагностическое оборудование'])->id,
        'manufacturer_id' => Manufacturer::factory()->create()->id,
        'staff_id' => Staff::factory()->create()->id,
        'serial_number' => 'US-2024-0042',
        'date_purchase' => '2024-03-01',
        'calibration_date' => '2026-09-01',
        'repair' => 'Не требуется',
        'term_date' => '2031-03-01',
        'term_notify_days' => 30,
        'upcoming_service_at' => 90,
        'location' => 'Кабинет УЗИ',
        'price' => 185_000_000,
        'status' => EquipmentStatus::Broken,
    ]);
    $batch = MainWarehouseMedication::factory()->create(['packaging_type' => PackagingType::Vial]);
    $migration = require database_path('migrations/2025_11_20_110200_store_english_values_of_equipment_status_and_packaging_type.php');

    $migration->down();

    expect(DB::table('equipment')->where('id', $equipment->id)->value('status'))->toBe('сломан')
        ->and(DB::table('main_warehouse_medications')->where('id', $batch->id)->value('packaging_type'))->toBe('флакон')
        ->and(enumColumnValues('equipment', 'status'))->toEqualCanonicalizing(['работает', 'сломан']);

    $migration->up();
    $migration->up();

    expect($equipment->fresh()->status)->toBe(EquipmentStatus::Broken)
        ->and($batch->fresh()->packaging_type)->toBe(PackagingType::Vial)
        ->and(enumColumnValues('medication_packagings', 'packaging_type'))->toEqualCanonicalizing(PackagingType::values());
});
