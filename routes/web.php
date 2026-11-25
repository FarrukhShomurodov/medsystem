<?php

use App\Http\Controllers\Dashboard\AdminAuthController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\AllergyController;
use App\Http\Controllers\Dashboard\CashierController;
use App\Http\Controllers\Dashboard\ChronicDiseaseController;
use App\Http\Controllers\Dashboard\CityController;
use App\Http\Controllers\Dashboard\DangerousCombinationController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DepartmentController;
use App\Http\Controllers\Dashboard\DiagnosisController;
use App\Http\Controllers\Dashboard\DocumentController;
use App\Http\Controllers\Dashboard\EquipmentController;
use App\Http\Controllers\Dashboard\EquipmentTypeController;
use App\Http\Controllers\Dashboard\ExaminationMapController;
use App\Http\Controllers\Dashboard\FloorController;
use App\Http\Controllers\Dashboard\HouseholdMaterialController;
use App\Http\Controllers\Dashboard\MainWarehouseController;
use App\Http\Controllers\Dashboard\ManufacturerController;
use App\Http\Controllers\Dashboard\MedicalDeviceController;
use App\Http\Controllers\Dashboard\MedicalSupplyController;
use App\Http\Controllers\Dashboard\MedicationController;
use App\Http\Controllers\Dashboard\MedicationGroupController;
use App\Http\Controllers\Dashboard\NurseWindowController;
use App\Http\Controllers\Dashboard\OfficeController;
use App\Http\Controllers\Dashboard\PatientAssignmentController;
use App\Http\Controllers\Dashboard\PatientController;
use App\Http\Controllers\Dashboard\PatientDiagnosisController;
use App\Http\Controllers\Dashboard\PatientFileController;
use App\Http\Controllers\Dashboard\PatientInspectionController;
use App\Http\Controllers\Dashboard\PatientMedicationController;
use App\Http\Controllers\Dashboard\PatientParameterController;
use App\Http\Controllers\Dashboard\PatientProcedureController;
use App\Http\Controllers\Dashboard\PatientServiceController;
use App\Http\Controllers\Dashboard\PharmacyController;
use App\Http\Controllers\Dashboard\PharmacyTypeController;
use App\Http\Controllers\Dashboard\ProcedureController;
use App\Http\Controllers\Dashboard\ReceptionController;
use App\Http\Controllers\Dashboard\RegionController;
use App\Http\Controllers\Dashboard\RegistrarController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\Dashboard\ServiceGroupController;
use App\Http\Controllers\Dashboard\ServiceMethodController;
use App\Http\Controllers\Dashboard\ServiceTypeController;
use App\Http\Controllers\Dashboard\SpecialtyController;
use App\Http\Controllers\Dashboard\StaffController;
use App\Http\Controllers\Dashboard\StaffPositionController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\SupplyRequestController;
use App\Http\Controllers\Dashboard\SurveyController;
use App\Http\Controllers\Dashboard\SurveyResponseController;
use App\Http\Controllers\Dashboard\TransferController;
use App\Http\Controllers\Dashboard\WardRoomController;
use App\Http\Controllers\Dashboard\WarehouseAccountingController;
use App\Http\Controllers\Dashboard\WarehouseController;
use App\Http\Controllers\Dashboard\WarehouseTypeController;
use App\Models\Reception;
use Illuminate\Support\Facades\Route;

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])
    ->name('dashboard.login');
Route::post('admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:login');

/*
| Every dashboard route requires a permission of the "admin" guard (main_admin holds all of them):
| resource controllers declare one per action in their HasMiddleware::middleware(), every other
| route carries its own below. Pages reached from several screens accept the permission of any
| of them ("a|b"). tests/Feature/Authorization keeps this true for every route.
*/
Route::prefix('dashboard')->middleware('auth:admin')->group(function (): void {
    // Any signed-in account may sign out.
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // home
    Route::get('', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view_dashboard');

    Route::resource('admins', AdminController::class)->except('show');

    // cashier
    Route::middleware('permission:view_cashier')->group(function (): void {
        Route::get('cashier', [CashierController::class, 'index'])
            ->name('cashier.index');

        Route::get('cashier/export', [CashierController::class, 'export'])
            ->name('cashier.export');
    });

    Route::put('/cashier/update-all', [CashierController::class, 'updateAll'])
        ->name('cashier.updateAll')
        ->middleware('permission:accept_payments');

    // department
    Route::prefix('departments')->group(function (): void {
        Route::resource('', DepartmentController::class)->names('departments')
            ->parameter('', 'department')
            ->except('show');

        Route::resource('/rooms', WardRoomController::class)
            ->names('departments.room')
            ->except('show');
    });

    // equipments
    Route::resource('/equipments', EquipmentController::class)->names('equipment')->except('show');
    Route::resource('/equipment-types', EquipmentTypeController::class)->names('equipment.types')->parameter(
        'equipment-types',
        'type',
    )->except('show');

    // staff
    Route::prefix('staffs')->group(function (): void {
        Route::resource('/positions', StaffPositionController::class)
            ->names('staffs.position')
            ->except('show');

        Route::resource('/specialties', SpecialtyController::class)->except('show');

        Route::resource('list', StaffController::class)->names('staffs')
            ->parameter('list', 'staff');
    });

    // supplies
    Route::resource('supplies', MedicalSupplyController::class)->except('show');

    // receptions
    Route::get('receptions-appointment', [ReceptionController::class, 'appointment'])
        ->name('receptions.appointment')
        ->middleware('permission:view_appointment_receptions');

    Route::get('receptions/list', [ReceptionController::class, 'index'])
        ->name('receptions.index')
        ->middleware('permission:list_receptions');

    // A doctor's account opens only its own calendar (ReceptionPolicy).
    Route::get('receptions-doctor-calendar/{staff}/{service}', [ReceptionController::class, 'doctorCalendar'])
        ->whereNumber(['staff', 'service'])
        ->name('receptions.doctor.calendar')
        ->middleware('permission:view_reception_doctor_calendar')
        ->can('workOnCalendar', [Reception::class, 'staff']);

    // suppliers
    Route::resource('suppliers', SupplierController::class)->except('show');

    Route::resource('manufacturers', ManufacturerController::class)->except('show');

    // warehouse
    Route::prefix('warehouses')->group(function (): void {
        Route::resource('list', WarehouseController::class)
            ->names('warehouses')
            ->parameter('list', 'warehouse');

        Route::resource('types', WarehouseTypeController::class)
            ->names('warehouses.type')
            ->except('show');

        Route::get('medications', [WarehouseController::class, 'medications'])
            ->name('warehouses.medications')
            ->middleware('permission:view_warehouse_medications');

        Route::get('medication-show/{accounting}', [WarehouseAccountingController::class, 'show'])
            ->name('warehouse.medication.show')
            ->middleware('permission:view_warehouse_medication_detail');

        Route::get('supplies', [WarehouseController::class, 'supplies'])
            ->name('warehouses.supplies')
            ->middleware('permission:view_warehouse_supplies');

        // remains
        Route::get('remains-show/', [WarehouseController::class, 'remains'])
            ->name('warehouse.remains')
            ->middleware('permission:view_warehouse_remains');

        // supply requests to the main warehouse
        Route::middleware('permission:create_supply_requests')->group(function (): void {
            Route::get('order-show/', [WarehouseController::class, 'orderShow'])
                ->name('warehouse.order');

            Route::post('supply-requests/', [SupplyRequestController::class, 'store'])
                ->name('supply-requests.store');
        });
    });

    // pharmacy
    Route::prefix('pharmacy')->group(function (): void {
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
    Route::resource('transfers', TransferController::class)->except('show');
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

    // medications
    Route::prefix('medication')->group(function (): void {
        Route::resource('/list', MedicationController::class)->names('medications')
            ->parameter('list', 'medication');
        Route::resource('/groups', MedicationGroupController::class)->names('medication.groups')->except('show');
    });

    // services
    Route::prefix('services')->group(function (): void {
        Route::resource('list', ServiceController::class)->names('services')
            ->parameter('list', 'service')
            ->except('show');

        Route::resource('assign-patient', PatientServiceController::class)->names('patient.services')->parameter(
            'assign-patient',
            'service',
        )->except('show');

        Route::resource('types', ServiceTypeController::class)->names('service.types')->except('show');
        Route::resource('procedures', ProcedureController::class)->except('show');
        Route::resource('methods', ServiceMethodController::class)->names('service.methods')->except('show');

        Route::resource('groups', ServiceGroupController::class)->names('service.groups')->except('show');
    });

    Route::resource('regions', RegionController::class)->except('show');
    Route::resource('cities', CityController::class)->except('show');
    Route::resource('allegories', AllergyController::class)->parameters(['allegories' => 'allergy'])->except('show');
    Route::resource('diseases', ChronicDiseaseController::class)->except('show');
    Route::resource('devices', MedicalDeviceController::class)->except('show');
    Route::resource('patients', PatientController::class);
    Route::get('patients/{patient}/files/{file}', [PatientFileController::class, 'show'])
        ->scopeBindings()
        ->name('patients.files.show')
        ->middleware('permission:view_patients|edit_patients');

    // register: the services, receptions and procedures card of the patient page
    Route::post('/registrar/{patient}/confirm', [RegistrarController::class, 'confirm'])
        ->name('registrar.confirm')
        ->middleware('permission:confirm_patient_registration');
    Route::get('/registrar/{patient}/export', [RegistrarController::class, 'export'])
        ->name('registrar.export')
        ->middleware('permission:view_patients');

    // medication
    Route::post('patient-medication', [PatientMedicationController::class, 'store'])->name('patient.medication.store');
    Route::get('patient-medication/edit/{medication}', [PatientMedicationController::class, 'edit'])->name('patient.medication.edit');
    Route::put('patient-medication/edit/{medication}', [PatientMedicationController::class, 'update'])->name('patient.medication.update');

    // procedures
    Route::post('patient-procedures', [PatientProcedureController::class, 'store'])->name('patient.procedures.store');

    // diagnoses
    Route::post('patient/diagnoses', [PatientDiagnosisController::class, 'store'])
        ->name('patient.diagnoses.store')
        ->middleware('permission:create_patient_diagnoses');

    // patient assignment (a referral to a service)
    Route::post('patient-assignment', [PatientAssignmentController::class, 'store'])
        ->name('patient.assignment.store')
        ->middleware('permission:create_patient_services');

    // sister-window
    Route::get('sister-window', [NurseWindowController::class, 'index'])->name('sister.window.index')
        ->middleware('permission:sister_window_show');

    Route::get('sister-window/show/{patient}', [NurseWindowController::class, 'show'])->name('sister.window.show')
        ->middleware('permission:sister_window_show');

    Route::post('patient-inspection', [PatientInspectionController::class, 'store'])->name('patient.inspection.store')
        ->middleware('permission:patient_inspection_store');

    // examination maps: permissions and the active-map check in ExaminationMapController::middleware()
    Route::resource('maps', ExaminationMapController::class)
        ->except(['index', 'create']);

    Route::get('patient/map/create/{patient}', [ExaminationMapController::class, 'create'])
        ->name('patient.map.create');

    // medical documents: filled in from the examination map forms and the nurse window
    Route::middleware('permission:create_patient_map|edit_maps|sister_window_show')->group(function (): void {
        Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');

        Route::post('document/response', [DocumentController::class, 'store'])->name('document.response.store');
    });

    // A filled-in document's own page and its deletion: DocumentResponsePolicy in DocumentController.
    Route::get('document/response/answers/{documentResponse}', [DocumentController::class, 'showAnswer'])
        ->name('documents.show.answer')
        ->middleware('permission:create_patient_map|edit_maps');

    Route::delete('document/response/answers/delete/{documentResponse}', [DocumentController::class, 'delete'])
        ->name('documents.show.answer.delete')
        ->middleware('permission:create_patient_map');

    // service import
    Route::middleware('permission:create_services')->group(function (): void {
        Route::get('/services/import', [ServiceController::class, 'showImportServices'])->name('services.import.show');
        Route::post('/services/import', [ServiceController::class, 'importServices'])->name('services.import');
    });

    // dangerous combination import (medications list)
    Route::middleware('permission:create_medications')->group(function (): void {
        Route::get('/dangerous-combination/import', [DangerousCombinationController::class, 'showImport'])->name('dangerous.combination.import.show');
        Route::post('/dangerous-combination/import', [DangerousCombinationController::class, 'import'])->name('dangerous.combination.import');
    });

    // diagnoses
    Route::resource('/diagnoses', DiagnosisController::class)->names('diagnoses')->except('show');

    // diagnosis import
    Route::middleware('permission:create_diagnoses')->group(function (): void {
        Route::get('/diagnosis/import', [DiagnosisController::class, 'showImportDiagnosis'])->name('diagnoses.import.show');
        Route::post('/diagnosis/import', [DiagnosisController::class, 'importDiagnosis'])->name('diagnoses.import');
    });

    // patient vital parameters: the patient's normal values (patient page) and measurements (nurse window)
    Route::post('/patient-parameter/{patient}', [PatientParameterController::class, 'storeNormalVitalParameter'])
        ->name('patient.normal.vital.parameter.store')
        ->middleware('permission:edit_patients');

    Route::post('/patients/{patient}/parameters', [PatientParameterController::class, 'store'])
        ->name('patient.vital.parameters.store')
        ->middleware('permission:sister_window_show');

    // household materials
    Route::resource('/household-materials', HouseholdMaterialController::class)->names(
        'household.materials',
    )->parameter('household-materials', 'material')->except('show');

    // main warehouse
    Route::name('main.warehouse.')->prefix('main-warehouse')->group(function (): void {
        Route::middleware('permission:main_warehouse_remain')->group(function (): void {
            Route::get('remains', [MainWarehouseController::class, 'remains'])->name('remains');
            Route::get('medication/{batch}', [MainWarehouseController::class, 'showMedicationRemain'])
                ->name('show.medication.remain');
        });

        Route::middleware('permission:edit_main_warehouse')->group(function (): void {
            Route::get('receipts-goods', [MainWarehouseController::class, 'receiptsGoods'])->name('receipts.goods.index');
            Route::post('receipts-goods', [MainWarehouseController::class, 'receiptsGoodsStore'])->name('receipts.goods.store');
            // {type} binds a StockSection: an unknown section is a 404.
            Route::delete('receipts-discard/{type}/{id}', [MainWarehouseController::class, 'discardGood'])
                ->whereNumber('id')
                ->name('receipts.goods.discard');
        });
    });

    // supply requests of the department warehouses, handled by the main warehouse
    Route::get('supply-requests/orders', [SupplyRequestController::class, 'index'])
        ->name('supply-requests.index')
        ->middleware('permission:main_warehouse_remain');
    Route::post('supply-requests/{supplyRequest}/approve', [SupplyRequestController::class, 'approve'])
        ->name('supply-requests.approve')
        ->middleware('permission:approve_supply_requests');
    // Rejecting is the other answer to the same request, so it takes the same permission.
    Route::post('supply-requests/{supplyRequest}/reject', [SupplyRequestController::class, 'reject'])
        ->name('supply-requests.reject')
        ->middleware('permission:approve_supply_requests');

    // offices
    Route::resource('/offices', OfficeController::class)->except('show');
    Route::resource('/floors', FloorController::class);
});
