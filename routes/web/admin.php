<?php

use App\Http\Controllers\Dashboard\{AdminAuthController,
    AdminController,
    AllegoryController,
    ChronicDiseaseController,
    CityController,
    DashboardController,
    DepartmentController,
    DiagnosisController,
    DocumentController,
    EquipmentController,
    EquipmentTypeController,
    ExaminationMapController,
    HouseholdMaterialController,
    MainWarehouseController,
    ManufacturerController,
    MedicalDeviceController,
    MedicalSupplyController,
    MedicationController,
    MedicationGroupController,
    PatientAssignmentController,
    PatientController,
    PatientDiagnosisController,
    PatientInspectionController,
    PatientMedicationController,
    PatientParameterController,
    PatientProcedureController,
    PatientServiceController,
    PharmacyController,
    PharmacyTypeController,
    ProcedureController,
    ReceptionController,
    RegionController,
    ServiceCalendarController,
    ServiceController,
    ServiceGroupController,
    ServiceMethodController,
    ServiceTypeController,
    SisterWindowController,
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


    // department
    Route::prefix('departments')->group(function () {
        Route::resource('', DepartmentController::class)->names('departments')
            ->parameter('', 'department')
            ->except('show');

        Route::resource('/rooms', WardRoomController::class)
            ->names('departments.room');
    });

    // equipments
    Route::resource('/equipments', EquipmentController::class)->names('equipment');
    Route::resource('/equipment-types', EquipmentTypeController::class)->names('equipment.types')->parameter(
        'equipment-types',
        'type'
    );

    // staff
    Route::prefix('staffs')->group(function () {
        Route::resource('/positions', StaffPositionController::class)
            ->names('staffs.position');

        Route::resource('/specialties', SpecialtyController::class);

        Route::resource('list', StaffController::class)->names('staffs')
            ->parameter('list', 'staff');
    });

    // supplies
    Route::resource('supplies', MedicalSupplyController::class);

    // receptions
    Route::get('receptions-appointment', [ReceptionController::class, 'appointment'])
        ->name('receptions.appointment')
        ->middleware('permission:view_appointment_receptions');

    Route::get('receptions/list', [ReceptionController::class, 'index'])
        ->name('receptions.index')
        ->middleware('permission:view_receptions');

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

        Route::get('medication-show/{accounting}', [WarehouseAccountingController::class, 'show'])->name(
            'warehouse.medication.show'
        );

        Route::get('supplies', [WarehouseController::class, 'supplies'])
            ->name('warehouses.supplies')
            ->middleware('permission:view_warehouse_supplies');


        Route::get('order-show/', [WarehouseController::class, 'orderShow'])->name(
            'warehouse.order'
        );
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
        ->name('response.survey.store')
        ->middleware('permission:store_survey_response');

    // analyze
//    Route::resource('analyzes', AnalyzeController::class);

    // analyze result
//    Route::get('analyze-result/{analyze}', [AnalyzeResultController::class, 'create'])
//        ->name('analyze.result')
//        ->middleware('permission:create_analyze_result');
//    Route::post('analyze-result/{analyze}', [AnalyzeResultController::class, 'store'])
//        ->name('analyze.result.store')
//        ->middleware('permission:store_analyze_result');
//    Route::get('analyze-result-show/{analyze}', [AnalyzeResultController::class, 'show'])
//        ->name('analyze.result.show')
//        ->middleware('permission:show_analyze_result');
//    Route::get('analyze-result-edit/{analyze}', [AnalyzeResultController::class, 'edit'])
//        ->name('analyze.result.edit')
//        ->middleware('permission:edit_analyze_result');
//    Route::put('analyze-result/{analyze}', [AnalyzeResultController::class, 'update'])
//        ->name('analyze.result.update')
//        ->middleware('permission:update_analyze_result');

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

        Route::resource('assign-patient', PatientServiceController::class)->names('patient.services')->parameter(
            'assign-patient',
            'service'
        )->except('show');

        Route::resource('types', ServiceTypeController::class)->names('service.types');
        Route::resource('procedures', ProcedureController::class);
        Route::resource('methods', ServiceMethodController::class)->names('service.methods');

        Route::resource('groups', ServiceGroupController::class)->names('service.groups');
    });

//    Route::resource('laboratories', LaboratoryController::class);
//    Route::resource('methods', AnalyzeMethodController::class);
//    Route::resource('types', AnalyzeTypeController::class);
    Route::resource('regions', RegionController::class);
    Route::resource('cities', CityController::class);
    Route::resource('allegories', AllegoryController::class);
    Route::resource('diseases', ChronicDiseaseController::class);
    Route::resource('devices', MedicalDeviceController::class);
    Route::resource('patients', PatientController::class);

    // medication
    Route::post('patient-medication', [PatientMedicationController::class, 'store'])->name('patient.medication.store');

    // procedures
    Route::post('patient-procedures', [PatientProcedureController::class, 'store'])->name('patient.procedures.store');

    // diagnosises
    Route::post('patient/diagnoses', [PatientDiagnosisController::class, 'store'])->name('patient.diagnoses.store');

    // patient assignment
    Route::post('patient-assignment', [PatientAssignmentController::class, 'store'])->name('patient.assignment.store');

    // sister-window
    Route::get('sister-window', [SisterWindowController::class, 'index'])->name('sister.window.index')
        ->middleware('permission:sister_window_show');

    Route::get('sister-window/show/{patient}', [SisterWindowController::class, 'show'])->name('sister.window.show')
        ->middleware('permission:sister_window_show');

    Route::post('patient-inspection', [PatientInspectionController::class, 'store'])->name('patient.inspection.store')
        ->middleware('permission:patient_inspection_store');

    // permissions
    Route::resource('maps', ExaminationMapController::class)
        ->except(['index', 'create'])
        ->middleware(ExaminationMapIsActive::class);

    Route::get('patient/map/create/{patient}', [ExaminationMapController::class, 'create'])
        ->name('patient.map.create')
        ->middleware(ExaminationMapIsActive::class);


    // documents
    Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');

    Route::post('document/response', [DocumentController::class, 'store'])->name('document.response.store');
    Route::get('document/response/answers/{documentResponse}', [DocumentController::class, 'showAnswer'])->name(
        'documents.show.answer'
    );

    Route::get('document/response/answers/delete/{documentResponse}', [DocumentController::class, 'delete'])->name(
        'documents.show.answer.delete'
    );

    // service calendar
    Route::get('service/calendar/{type}/patient/{patient}', [ServiceCalendarController::class, 'index'])->name(
        'service.calendar.index'
    );

    Route::get('/services/import', [ServiceController::class, 'showImportServices'])->name('services.import.show');
    Route::post('/services/import', [ServiceController::class, 'importServices'])->name('services.import');

    // diagnosis
    Route::resource('/diagnoses', DiagnosisController::class)->names('diagnoses');

    // patient vital parameters
    Route::post('/patient-parameter/{patient}', [PatientParameterController::class, 'storeNormalVitalParameter'])->name(
        'patient.normal.vital.parameter.store'
    );

    Route::post('/patients/{patient}/parameters', [PatientParameterController::class, 'store'])
        ->name('patient.vital.parameters.store');


    // household materials
    Route::resource('/household-materials', HouseholdMaterialController::class)->names(
        'household.materials'
    )->parameter('household-materials', 'material');

    // main warehouse
    Route::name('main.warehouse.')->prefix('main-warehouse')->group(function () {
        Route::get('remains', [MainWarehouseController::class, 'remains'])->name('remains');
        Route::get('receipts-goods', [MainWarehouseController::class, 'receiptsGoods'])->name('receipts.goods.index');
        Route::post('receipts-goods', [MainWarehouseController::class, 'receiptsGoodsStore'])->name('receipts.goods.store');
    });
});
