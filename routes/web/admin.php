<?php

use App\Http\Controllers\Dashboard\{
    AdminAuthController,
    AdminController,
    AllegoryController,
    AnalyzeController,
    AnalyzeMethodController,
    AnalyzeResultController,
    AnalyzeTypeController,
    ChronicDiseaseController,
    CityController,
    DashboardController,
    DepartmentController,
    DepartmentTypeController,
    EquipmentController,
    ExaminationMapController,
    LaboratoryController,
    ManufacturerController,
    MedicalDeviceController,
    MedicalSupplyController,
    MedicationController,
    MedicationGroupController,
    PatientController,
    PatientServiceController,
    PharmacyController,
    PharmacyTypeController,
    ProcedureController,
    ReceptionController,
    RegionController,
    ServiceCategoryController,
    ServiceController,
    ServiceMethodController,
    SpecialtyController,
    StaffController,
    StaffPositionController,
    SupplierController,
    SurveyController,
    SurveyResponseController,
    TransferController,
    WardRoomController,
    WarehouseAccountingController,
    WarehouseController,
    WarehouseTypeController
};

use App\Http\Middleware\ExaminationMapIsActive;
use Illuminate\Support\Facades\Route;

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])
    ->name('dashboard.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::prefix('dashboard')->middleware('auth:admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // home
    Route::get('', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view_dashboard');

    Route::resource('admins', AdminController::class);

    Route::resource('specialties', SpecialtyController::class);

    // department
    Route::prefix('departments')->group(function () {
        Route::resource('', DepartmentController::class)->names('departments')
            ->parameter('', 'department')
            ->except('show');

        Route::resource('/types', DepartmentTypeController::class)
            ->names('departments.type');

        Route::resource('/rooms', WardRoomController::class)
            ->names('departments.room');
    });

    // equipments
    Route::resource('/equipments', EquipmentController::class)->names('equipment');

    // staff
    Route::prefix('staffs')->group(function () {
        Route::resource('', StaffController::class)->names('staffs')
            ->parameter('', 'staff')
            ->except('show');

        Route::resource('/positions', StaffPositionController::class)
            ->names('staffs.position');
    });

    // supplies
    Route::resource('supplies', MedicalSupplyController::class);

    // receptions
    Route::resource('receptions', ReceptionController::class);
    Route::get('receptions-doctor-calendar/{staff}', [ReceptionController::class, 'doctorCalendar'])
        ->name('receptions.doctor.calendar')
        ->middleware('permission:view_reception_doctor_calendar');

    // suppliers
    Route::resource('suppliers', SupplierController::class);

    Route::resource('manufacturers', ManufacturerController::class);

    // warehouse
    Route::prefix('warehouses')->group(function () {
        Route::resource('list', WarehouseController::class)
            ->names('warehouses')
            ->parameter('list', 'warehouse');

        Route::resource('types', WarehouseTypeController::class)
            ->names('warehouses.type')
            ->except('show');

        Route::get('medications', [WarehouseController::class, 'medications'])
            ->name('warehouses.medications')
            ->middleware('permission:view_warehouse_medications');

        Route::get('medication-show/{accounting}', [WarehouseAccountingController::class, 'show'])->name('warehouse.medication.show');

        Route::get('supplies', [WarehouseController::class, 'supplies'])
            ->name('warehouses.supplies')
            ->middleware('permission:view_warehouse_supplies');
    });

    // pharmacy
    Route::prefix('pharmacy')->group(function () {
        Route::resource('/', PharmacyController::class)->names('pharmacies')
            ->parameter('', 'pharmacy')
            ->except('show');
        Route::resource('/types', PharmacyTypeController::class)
            ->names('pharmacy.types')
            ->except('show');
        Route::get('medications/{pharmacy}', [PharmacyController::class, 'medications'])
            ->name('pharmacy.medications')
            ->middleware('permission:view_pharmacy_medications');
    });

    // transfers
    Route::resource('transfers', TransferController::class);
    Route::get('transfer-medications/{transfer}', [TransferController::class, 'medications'])
        ->name('transfer.medications')
        ->middleware('permission:view_transfer_medications');

    // surveys
    Route::resource('surveys', SurveyController::class)->names('surveys');

    // surveys response
    Route::get('survey-response/{map}', [SurveyResponseController::class, 'create'])
        ->name('map.survey')
        ->middleware('permission:create_survey_response');
    Route::post('survey-response', [SurveyResponseController::class, 'store'])
        ->name('map.survey.store')
        ->middleware('permission:store_survey_response');

    // analyze
    Route::resource('analyzes', AnalyzeController::class);

    // analyze result
    Route::get('analyze-result/{analyze}', [AnalyzeResultController::class, 'create'])
        ->name('analyze.result')
        ->middleware('permission:create_analyze_result');
    Route::post('analyze-result/{analyze}', [AnalyzeResultController::class, 'store'])
        ->name('analyze.result.store')
        ->middleware('permission:store_analyze_result');
    Route::get('analyze-result-show/{analyze}', [AnalyzeResultController::class, 'show'])
        ->name('analyze.result.show')
        ->middleware('permission:show_analyze_result');
    Route::get('analyze-result-edit/{analyze}', [AnalyzeResultController::class, 'edit'])
        ->name('analyze.result.edit')
        ->middleware('permission:edit_analyze_result');
    Route::put('analyze-result/{analyze}', [AnalyzeResultController::class, 'update'])
        ->name('analyze.result.update')
        ->middleware('permission:update_analyze_result');

    // medications
    Route::prefix('medication')->group(function () {
        Route::resource('/list', MedicationController::class)->names('medications')
            ->parameter('list', 'medication');
        Route::resource('/groups', MedicationGroupController::class)->names('medication.groups');
    });

    // services
    Route::prefix('services')->group(function () {
        Route::resource('list', ServiceController::class)->names('services')
            ->parameter('list', 'service')
            ->except('show');
        Route::resource('assign-patient', PatientServiceController::class)->names('patient.services')->parameter('assign-patient', 'service');
        Route::resource('categories', ServiceCategoryController::class);
        Route::resource('procedures', ProcedureController::class);
        Route::resource('methods', ServiceMethodController::class)->names('service.methods');
    });

    Route::resource('laboratories', LaboratoryController::class);
    Route::resource('methods', AnalyzeMethodController::class);
    Route::resource('types', AnalyzeTypeController::class);
    Route::resource('regions', RegionController::class);
    Route::resource('cities', CityController::class);
    Route::resource('allegories', AllegoryController::class);
    Route::resource('diseases', ChronicDiseaseController::class);
    Route::resource('devices', MedicalDeviceController::class);
    Route::resource('patients', PatientController::class);

    // permissions
    Route::resource('maps', ExaminationMapController::class)
        ->except(['index', 'create'])
        ->middleware(ExaminationMapIsActive::class);

    Route::get('patient/map/create/{patient}', [ExaminationMapController::class, 'create'])
        ->name('patient.map.create')
        ->middleware(ExaminationMapIsActive::class);
});
