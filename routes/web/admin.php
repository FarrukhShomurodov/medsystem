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
    ->name('dashboard.login'); // нет необходимости в permission
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::prefix('dashboard')->middleware('auth:admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // home
    Route::get('', [DashboardController::class, 'index'])
        ->name('dashboard');
//        ->middleware('can:view_dashboard');

    Route::resource('admins', AdminController::class)->middleware([
        'index' => 'can:view_admins',
        'create' => 'can:create_admins',
        'store' => 'can:create_admins',
        'edit' => 'can:edit_admins',
        'update' => 'can:edit_admins',
        'destroy' => 'can:delete_admins',
    ]);

    Route::resource('specialties', SpecialtyController::class)->middleware([
        'index' => 'can:view_specialties',
        'create' => 'can:create_specialties',
        'store' => 'can:create_specialties',
        'edit' => 'can:edit_specialties',
        'update' => 'can:edit_specialties',
        'destroy' => 'can:delete_specialties',
    ]);

    // department
    Route::prefix('departments')->group(function () {
        Route::resource('', DepartmentController::class)->names('departments')
            ->parameter('', 'department')
            ->except('show')
            ->middleware([
                'index' => 'can:view_departments',
                'create' => 'can:create_departments',
                'store' => 'can:create_departments',
                'edit' => 'can:edit_departments',
                'update' => 'can:edit_departments',
                'destroy' => 'can:delete_departments',
            ]);

        Route::resource('/types', DepartmentTypeController::class)
            ->names('departments.type')
            ->middleware([
                'index' => 'can:view_department_types',
                'create' => 'can:create_department_types',
                'store' => 'can:create_department_types',
                'edit' => 'can:edit_department_types',
                'update' => 'can:edit_department_types',
                'destroy' => 'can:delete_department_types',
            ]);

        Route::resource('/rooms', WardRoomController::class)
            ->names('departments.room')
            ->middleware([
                'index' => 'can:view_department_rooms',
                'create' => 'can:create_department_rooms',
                'store' => 'can:create_department_rooms',
                'edit' => 'can:edit_department_rooms',
                'update' => 'can:edit_department_rooms',
                'destroy' => 'can:delete_department_rooms',
            ]);
    });

    // equipments
    Route::resource('/equipments', EquipmentController::class)->names('equipment')
        ->middleware([
            'index' => 'can:view_equipments',
            'create' => 'can:create_equipments',
            'store' => 'can:create_equipments',
            'edit' => 'can:edit_equipments',
            'update' => 'can:edit_equipments',
            'destroy' => 'can:delete_equipments',
        ]);

    // staff
    Route::prefix('staffs')->group(function () {
        Route::resource('', StaffController::class)->names('staffs')
            ->parameter('', 'staff')
            ->except('show')
            ->middleware([
                'index' => 'can:view_staffs',
                'create' => 'can:create_staffs',
                'store' => 'can:create_staffs',
                'edit' => 'can:edit_staffs',
                'update' => 'can:edit_staffs',
                'destroy' => 'can:delete_staffs',
            ]);

        Route::resource('/positions', StaffPositionController::class)
            ->names('staffs.position')
            ->middleware([
                'index' => 'can:view_staff_positions',
                'create' => 'can:create_staff_positions',
                'store' => 'can:create_staff_positions',
                'edit' => 'can:edit_staff_positions',
                'update' => 'can:edit_staff_positions',
                'destroy' => 'can:delete_staff_positions',
            ]);
    });

    // supplies
    Route::resource('supplies', MedicalSupplyController::class)
        ->middleware([
            'index' => 'can:view_supplies',
            'create' => 'can:create_supplies',
            'store' => 'can:create_supplies',
            'edit' => 'can:edit_supplies',
            'update' => 'can:edit_supplies',
            'destroy' => 'can:delete_supplies',
        ]);

    // receptions
    Route::resource('receptions', ReceptionController::class)
        ->middleware([
            'index' => 'can:view_receptions',
            'create' => 'can:create_receptions',
            'store' => 'can:create_receptions',
            'edit' => 'can:edit_receptions',
            'update' => 'can:edit_receptions',
            'destroy' => 'can:delete_receptions',
        ]);
    Route::get('receptions-doctor-calendar/{staff}', [ReceptionController::class, 'doctorCalendar'])
        ->name('receptions.doctor.calendar')
        ->middleware('can:view_reception_doctor_calendar');

    // suppliers
    Route::resource('suppliers', SupplierController::class)
        ->middleware([
            'index' => 'can:view_suppliers',
            'create' => 'can:create_suppliers',
            'store' => 'can:create_suppliers',
            'edit' => 'can:edit_suppliers',
            'update' => 'can:edit_suppliers',
            'destroy' => 'can:delete_suppliers',
        ]);

    Route::resource('manufacturers', ManufacturerController::class)
        ->middleware([
            'index' => 'can:view_manufacturers',
            'create' => 'can:create_manufacturers',
            'store' => 'can:create_manufacturers',
            'edit' => 'can:edit_manufacturers',
            'update' => 'can:edit_manufacturers',
            'destroy' => 'can:delete_manufacturers',
        ]);

    // warehouse
    Route::prefix('warehouses')->group(function () {
        Route::resource('list', WarehouseController::class)
            ->names('warehouses')
            ->parameter('list', 'warehouse')
            ->middleware([
                'index' => 'can:view_warehouses',
                'create' => 'can:create_warehouses',
                'store' => 'can:create_warehouses',
                'edit' => 'can:edit_warehouses',
                'update' => 'can:edit_warehouses',
                'destroy' => 'can:delete_warehouses',
            ]);

        Route::resource('types', WarehouseTypeController::class)
            ->names('warehouses.type')
            ->except('show')
            ->middleware([
                'index' => 'can:view_warehouse_types',
                'create' => 'can:create_warehouse_types',
                'store' => 'can:create_warehouse_types',
                'edit' => 'can:edit_warehouse_types',
                'update' => 'can:edit_warehouse_types',
                'destroy' => 'can:delete_warehouse_types',
            ]);

        Route::get('medications', [WarehouseController::class, 'medications'])
            ->name('warehouses.medications')
            ->middleware('can:view_warehouse_medications');

        Route::get('medication-show/{accounting}', [WarehouseAccountingController::class, 'show'])->name('warehouse.medication.show');

        Route::get('supplies', [WarehouseController::class, 'supplies'])
            ->name('warehouses.supplies')
            ->middleware('can:view_warehouse_supplies');
    });

    // pharmacy
    Route::prefix('pharmacy')->group(function () {
        Route::resource('/', PharmacyController::class)->names('pharmacies')
            ->parameter('', 'pharmacy')
            ->except('show')
            ->middleware([
                'index' => 'can:view_pharmacies',
                'create' => 'can:create_pharmacies',
                'store' => 'can:create_pharmacies',
                'edit' => 'can:edit_pharmacies',
                'update' => 'can:edit_pharmacies',
                'destroy' => 'can:delete_pharmacies',
            ]);
        Route::resource('/types', PharmacyTypeController::class)
            ->names('pharmacy.types')
            ->except('show')
            ->middleware([
                'index' => 'can:view_pharmacy_types',
                'create' => 'can:create_pharmacy_types',
                'store' => 'can:create_pharmacy_types',
                'edit' => 'can:edit_pharmacy_types',
                'update' => 'can:edit_pharmacy_types',
                'destroy' => 'can:delete_pharmacy_types',
            ]);
        Route::get('medications/{pharmacy}', [PharmacyController::class, 'medications'])
            ->name('pharmacy.medications')
            ->middleware('can:view_pharmacy_medications');
    });

    // transfers
    Route::resource('transfers', TransferController::class)
        ->middleware([
            'index' => 'can:view_transfers',
            'create' => 'can:create_transfers',
            'store' => 'can:create_transfers',
            'edit' => 'can:edit_transfers',
            'update' => 'can:edit_transfers',
            'destroy' => 'can:delete_transfers',
        ]);
    Route::get('transfer-medications/{transfer}', [TransferController::class, 'medications'])
        ->name('transfer.medications')
        ->middleware('can:view_transfer_medications');

    // surveys
    Route::resource('surveys', SurveyController::class)->names('surveys')
        ->middleware([
            'index' => 'can:view_surveys',
            'create' => 'can:create_surveys',
            'store' => 'can:create_surveys',
            'edit' => 'can:edit_surveys',
            'update' => 'can:edit_surveys',
            'destroy' => 'can:delete_surveys',
        ]);

    // surveys response
    Route::get('survey-response/{map}', [SurveyResponseController::class, 'create'])
        ->name('map.survey')
        ->middleware('can:create_survey_response');
    Route::post('survey-response', [SurveyResponseController::class, 'store'])
        ->name('map.survey.store')
        ->middleware('can:store_survey_response');

    // analyze
    Route::resource('analyzes', AnalyzeController::class)
        ->middleware([
            'index' => 'can:view_analyzes',
            'create' => 'can:create_analyzes',
            'store' => 'can:create_analyzes',
            'edit' => 'can:edit_analyzes',
            'update' => 'can:edit_analyzes',
            'destroy' => 'can:delete_analyzes',
        ]);

    // analyze result
    Route::get('analyze-result/{analyze}', [AnalyzeResultController::class, 'create'])
        ->name('analyze.result')
        ->middleware('can:create_analyze_result');
    Route::post('analyze-result/{analyze}', [AnalyzeResultController::class, 'store'])
        ->name('analyze.result.store')
        ->middleware('can:store_analyze_result');
    Route::get('analyze-result-show/{analyze}', [AnalyzeResultController::class, 'show'])
        ->name('analyze.result.show')
        ->middleware('can:show_analyze_result');
    Route::get('analyze-result-edit/{analyze}', [AnalyzeResultController::class, 'edit'])
        ->name('analyze.result.edit')
        ->middleware('can:edit_analyze_result');
    Route::put('analyze-result/{analyze}', [AnalyzeResultController::class, 'update'])
        ->name('analyze.result.update')
        ->middleware('can:update_analyze_result');

    // medications
    Route::prefix('medication')->group(function () {
        Route::resource('/list', MedicationController::class)->names('medications')
            ->parameter('list', 'medication')
            ->middleware([
                'index' => 'can:view_medications',
                'create' => 'can:create_medications',
                'store' => 'can:create_medications',
                'edit' => 'can:edit_medications',
                'update' => 'can:edit_medications',
                'destroy' => 'can:delete_medications',
            ]);
        Route::resource('/groups', MedicationGroupController::class)->names('medication.groups')
            ->middleware([
                'index' => 'can:view_medication_groups',
                'create' => 'can:create_medication_groups',
                'store' => 'can:create_medication_groups',
                'edit' => 'can:edit_medication_groups',
                'update' => 'can:edit_medication_groups',
                'destroy' => 'can:delete_medication_groups',
            ]);
    });

    // services
    Route::prefix('services')->group(function () {
        Route::resource('list', ServiceController::class)->names('services')
            ->parameter('list', 'service')
            ->except('show')
            ->middleware([
                'index' => 'can:view_services',
                'create' => 'can:create_services',
                'store' => 'can:create_services',
                'edit' => 'can:edit_services',
                'update' => 'can:edit_services',
                'destroy' => 'can:delete_services',
            ]);
        Route::resource('assign-patient', PatientServiceController::class)->names('patient.services')
            ->parameter('assign-patient', 'service')
            ->middleware([
                'index' => 'can:view_patient_services',
                'create' => 'can:create_patient_services',
                'store' => 'can:create_patient_services',
                'edit' => 'can:edit_patient_services',
                'update' => 'can:edit_patient_services',
                'destroy' => 'can:delete_patient_services',
            ]);
        Route::resource('categories', ServiceCategoryController::class)
            ->middleware([
                'index' => 'can:view_service_categories',
                'create' => 'can:create_service_categories',
                'store' => 'can:create_service_categories',
                'edit' => 'can:edit_service_categories',
                'update' => 'can:edit_service_categories',
                'destroy' => 'can:delete_service_categories',
            ]);
        Route::resource('procedures', ProcedureController::class)
            ->middleware([
                'index' => 'can:view_service_procedures',
                'create' => 'can:create_service_procedures',
                'store' => 'can:create_service_procedures',
                'edit' => 'can:edit_service_procedures',
                'update' => 'can:edit_service_procedures',
                'destroy' => 'can:delete_service_procedures',
            ]);
        Route::resource('methods', ServiceMethodController::class)->names('service.methods')
            ->middleware([
                'index' => 'can:view_service_methods',
                'create' => 'can:create_service_methods',
                'store' => 'can:create_service_methods',
                'edit' => 'can:edit_service_methods',
                'update' => 'can:edit_service_methods',
                'destroy' => 'can:delete_service_methods',
            ]);
    });

    Route::resource('laboratories', LaboratoryController::class)
        ->middleware([
            'index' => 'can:view_laboratories',
            'create' => 'can:create_laboratories',
            'store' => 'can:create_laboratories',
            'edit' => 'can:edit_laboratories',
            'update' => 'can:edit_laboratories',
            'destroy' => 'can:delete_laboratories',
        ]);

    Route::resource('methods', AnalyzeMethodController::class)
        ->middleware([
            'index' => 'can:view_analyze_methods',
            'create' => 'can:create_analyze_methods',
            'store' => 'can:create_analyze_methods',
            'edit' => 'can:edit_analyze_methods',
            'update' => 'can:edit_analyze_methods',
            'destroy' => 'can:delete_analyze_methods',
        ]);

    Route::resource('types', AnalyzeTypeController::class)
        ->middleware([
            'index' => 'can:view_analyze_types',
            'create' => 'can:create_analyze_types',
            'store' => 'can:create_analyze_types',
            'edit' => 'can:edit_analyze_types',
            'update' => 'can:edit_analyze_types',
            'destroy' => 'can:delete_analyze_types',
        ]);

    Route::resource('regions', RegionController::class)
        ->middleware([
            'index' => 'can:view_regions',
            'create' => 'can:create_regions',
            'store' => 'can:create_regions',
            'edit' => 'can:edit_regions',
            'update' => 'can:edit_regions',
            'destroy' => 'can:delete_regions',
        ]);

    Route::resource('cities', CityController::class)
        ->middleware([
            'index' => 'can:view_cities',
            'create' => 'can:create_cities',
            'store' => 'can:create_cities',
            'edit' => 'can:edit_cities',
            'update' => 'can:edit_cities',
            'destroy' => 'can:delete_cities',
        ]);

    Route::resource('allegories', AllegoryController::class)
        ->middleware([
            'index' => 'can:view_allegories',
            'create' => 'can:create_allegories',
            'store' => 'can:create_allegories',
            'edit' => 'can:edit_allegories',
            'update' => 'can:edit_allegories',
            'destroy' => 'can:delete_allegories',
        ]);

    Route::resource('diseases', ChronicDiseaseController::class)
        ->middleware([
            'index' => 'can:view_diseases',
            'create' => 'can:create_diseases',
            'store' => 'can:create_diseases',
            'edit' => 'can:edit_diseases',
            'update' => 'can:edit_diseases',
            'destroy' => 'can:delete_diseases',
        ]);

    Route::resource('devices', MedicalDeviceController::class)
        ->middleware([
            'index' => 'can:view_devices',
            'create' => 'can:create_devices',
            'store' => 'can:create_devices',
            'edit' => 'can:edit_devices',
            'update' => 'can:edit_devices',
            'destroy' => 'can:delete_devices',
        ]);

    Route::resource('patients', PatientController::class)
        ->middleware([
            'index' => 'can:view_patients',
            'create' => 'can:create_patients',
            'store' => 'can:create_patients',
            'edit' => 'can:edit_patients',
            'update' => 'can:edit_patients',
            'destroy' => 'can:delete_patients',
        ]);

    Route::resource('maps', ExaminationMapController::class)
        ->except(['index', 'create'])
        ->middleware(ExaminationMapIsActive::class);

    Route::get('patient/map/create/{patient}', [ExaminationMapController::class, 'create'])
        ->name('patient.map.create')
        ->middleware(ExaminationMapIsActive::class);
});
