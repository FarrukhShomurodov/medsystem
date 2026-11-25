<?php

use App\Models\Admin;
use App\Models\City;
use App\Models\DangerousCombination;
use App\Models\DayOfWeek;
use App\Models\Department;
use App\Models\Diagnosis;
use App\Models\Document;
use App\Models\ExaminationMap;
use App\Models\Floor;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseMedication;
use App\Models\Manufacturer;
use App\Models\Medication;
use App\Models\MedicationGroup;
use App\Models\Office;
use App\Models\Patient;
use App\Models\PatientDiagnosis;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Pharmacy;
use App\Models\PharmacyType;
use App\Models\Procedure;
use App\Models\Reception;
use App\Models\Region;
use App\Models\RoomBed;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceMethod;
use App\Models\ServiceType;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffAbsence;
use App\Models\StaffContact;
use App\Models\StaffDocument;
use App\Models\StaffPosition;
use App\Models\StaffProfessionalData;
use App\Models\StaffSchedule;
use App\Models\Supplier;
use App\Models\Survey;
use App\Models\SurveyType;
use App\Models\WardRoom;
use App\Models\Warehouse;
use App\Models\WarehouseType;
use Illuminate\Support\Facades\Hash;

it('persists a valid record', function (string $model): void {
    $record = $model::factory()->create();

    expect($record->getKey())->not->toBeNull()
        ->and($model::query()->whereKey($record->getKey())->exists())->toBeTrue();
})->with([
    'Admin' => Admin::class,
    'City' => City::class,
    'DangerousCombination' => DangerousCombination::class,
    'DayOfWeek' => DayOfWeek::class,
    'Department' => Department::class,
    'Diagnosis' => Diagnosis::class,
    'Document' => Document::class,
    'ExaminationMap' => ExaminationMap::class,
    'Floor' => Floor::class,
    'MainWarehouse' => MainWarehouse::class,
    'MainWarehouseMedication' => MainWarehouseMedication::class,
    'Manufacturer' => Manufacturer::class,
    'Medication' => Medication::class,
    'MedicationGroup' => MedicationGroup::class,
    'Office' => Office::class,
    'Patient' => Patient::class,
    'PatientDiagnosis' => PatientDiagnosis::class,
    'PatientMedication' => PatientMedication::class,
    'PatientProcedure' => PatientProcedure::class,
    'PatientService' => PatientService::class,
    'Pharmacy' => Pharmacy::class,
    'PharmacyType' => PharmacyType::class,
    'Procedure' => Procedure::class,
    'Reception' => Reception::class,
    'Region' => Region::class,
    'RoomBed' => RoomBed::class,
    'Service' => Service::class,
    'ServiceGroup' => ServiceGroup::class,
    'ServiceMethod' => ServiceMethod::class,
    'ServiceType' => ServiceType::class,
    'Specialty' => Specialty::class,
    'Staff' => Staff::class,
    'StaffAbsence' => StaffAbsence::class,
    'StaffContact' => StaffContact::class,
    'StaffDocument' => StaffDocument::class,
    'StaffPosition' => StaffPosition::class,
    'StaffProfessionalData' => StaffProfessionalData::class,
    'StaffSchedule' => StaffSchedule::class,
    'Supplier' => Supplier::class,
    'Survey' => Survey::class,
    'SurveyType' => SurveyType::class,
    'Warehouse' => Warehouse::class,
    'WarehouseType' => WarehouseType::class,
    'WardRoom' => WardRoom::class,
]);

it('registers a doctor the way StaffService does', function (): void {
    $doctor = Staff::factory()->doctor()->create();

    expect($doctor->contact)->not->toBeNull()
        ->and($doctor->document->is_work)->toBeTrue()
        ->and($doctor->professionalData->position->name)->toBe('Доктор')
        ->and($doctor->full_name)->toBe("{$doctor->last_name} {$doctor->name} {$doctor->middle_name}");
});

it('creates a main administrator with the main_admin role', function (): void {
    $admin = Admin::factory()->mainAdmin()->create();

    expect($admin->hasRole('main_admin', 'admin'))->toBeTrue();
});

it('hashes admin passwords through the model cast', function (): void {
    $admin = Admin::factory()->create(['password' => 'correct horse battery staple']);

    expect($admin->password)->not->toBe('correct horse battery staple')
        ->and(Hash::check('correct horse battery staple', $admin->password))->toBeTrue()
        ->and($admin->toArray())->not->toHaveKeys(['password', 'remember_token']);
});

it('gives pivot models with a surrogate key their id', function (string $model): void {
    expect($model::factory()->create()->id)->toBeInt();
})->with([
    'MainWarehouseMedication' => MainWarehouseMedication::class,
    'PatientMedication' => PatientMedication::class,
    'PatientProcedure' => PatientProcedure::class,
]);
