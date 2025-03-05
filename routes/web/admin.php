<?php

use App\Http\Controllers\Dashboard\AdminAuthController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\AllegoryController;
use App\Http\Controllers\Dashboard\AnalyzeController;
use App\Http\Controllers\Dashboard\AnalyzeMethodController;
use App\Http\Controllers\Dashboard\AnalyzeResultController;
use App\Http\Controllers\Dashboard\AnalyzeTypeController;
use App\Http\Controllers\Dashboard\ChronicDiseaseController;
use App\Http\Controllers\Dashboard\CityController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DepartmentController;
use App\Http\Controllers\Dashboard\EquipmentController;
use App\Http\Controllers\Dashboard\DepartmentTypeController;
use App\Http\Controllers\Dashboard\ExaminationMapController;
use App\Http\Controllers\Dashboard\LaboratoryController;
use App\Http\Controllers\Dashboard\MedicalDeviceController;
use App\Http\Controllers\Dashboard\MedicalSupplyController;
use App\Http\Controllers\Dashboard\MedicationController;
use App\Http\Controllers\Dashboard\PatientController;
use App\Http\Controllers\Dashboard\PatientServiceController;
use App\Http\Controllers\Dashboard\PharmacyController;
use App\Http\Controllers\Dashboard\PharmacyTypeController;
use App\Http\Controllers\Dashboard\ProcedureController;
use App\Http\Controllers\Dashboard\RegionController;
use App\Http\Controllers\Dashboard\ServiceCategoryController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\Dashboard\ServiceMethodController;
use App\Http\Controllers\Dashboard\ServiceStaffController;
use App\Http\Controllers\Dashboard\SpecialtyController;
use App\Http\Controllers\Dashboard\StaffController;
use App\Http\Controllers\Dashboard\StaffPositionController;
use App\Http\Controllers\Dashboard\SupplierController;
use App\Http\Controllers\Dashboard\TransferController;
use App\Http\Controllers\Dashboard\WarehouseController;
use App\Http\Controllers\Dashboard\WarehouseTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('dashboard.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::prefix('dashboard')->middleware('auth:admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::get('', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('admins', AdminController::class);

    Route::resource('specialties', SpecialtyController::class);

    // department
    Route::prefix('departments')->group(function () {
        Route::resource('', DepartmentController::class)->names('departments')->parameter('', 'department')->except('show');
        Route::resource('/types', DepartmentTypeController::class)->names('departments.type');
    });

    //equipments
    Route::resource('/equipments', EquipmentController::class)->names('equipment');

    // staff
    Route::prefix('staffs')->group(function () {
        Route::resource('', StaffController::class)->names('staffs')->parameter('', 'staff')->except('show');
        Route::resource('/positions', StaffPositionController::class)->names('staffs.position');
    });

    // supplies
    Route::resource('supplies', MedicalSupplyController::class);

    // suppliers
    Route::resource('suppliers', SupplierController::class);

    // warehouse
    Route::prefix('warehouses')->group(function () {
        Route::resource('/', WarehouseController::class)->names('warehouses')->parameter('', 'warehouse')->except('show');
        Route::resource('/types', WarehouseTypeController::class)->names('warehouses.type')->except('show');
    });

    // pharmacy
    Route::prefix('pharmacy')->group(function () {
        Route::resource('/', PharmacyController::class)->names('pharmacies')->parameter('', 'pharmacy')->except('show');
        Route::resource('/types', PharmacyTypeController::class)->names('pharmacy.types')->except('show');
        Route::get('medications/{pharmacy}', [PharmacyController::class, 'medications'])->name('pharmacy.medications');

    });

    // transfers
    Route::resource('transfers', TransferController::class);
    Route::get('transfer-medications/{transfer}', [TransferController::class, 'medications'])->name('transfer.medications');

    // analyze
    Route::resource('analyzes', AnalyzeController::class);

    // analyze result
    Route::get('analyze-result/{analyze}', [AnalyzeResultController::class, 'create'])->name('analyze.result');
    Route::post('analyze-result/{analyze}', [AnalyzeResultController::class, 'store'])->name('analyze.result.store');
    Route::get('analyze-result-show/{analyze}', [AnalyzeResultController::class, 'show'])->name('analyze.result.show');
    Route::get('analyze-result-edit/{analyze}', [AnalyzeResultController::class, 'edit'])->name('analyze.result.edit');
    Route::put('analyze-result/{analyze}', [AnalyzeResultController::class, 'update'])->name('analyze.result.update');

    // medications
    Route::resource('medications', MedicationController::class);

    // services
    Route::prefix('services')->group(function () {
        Route::resource('', ServiceController::class)->names('services')->parameter('', 'service')->except('show');
        Route::resource('assign-patient', PatientServiceController::class)->names('patient.services')->parameter('assign-patient', 'service');
        Route::resource('categories', ServiceCategoryController::class);
        Route::resource('procedures', ProcedureController::class);
        Route::resource('methods', ServiceMethodController::class)->names('service.methods');
        Route::resource('staffs', ServiceStaffController::class)->names('service.staffs');
    });

    Route::resource('laboratories', LaboratoryController::class);
    Route::resource('methods', AnalyzeMethodController::class);
//    Route::resource('equipments', MedicalEquipmentController::class);
    Route::resource('types', AnalyzeTypeController::class);

    Route::resource('regions', RegionController::class);
    Route::resource('cities', CityController::class);
    Route::resource('allegories', AllegoryController::class);
    Route::resource('diseases', ChronicDiseaseController::class);
    Route::resource('devices', MedicalDeviceController::class);

    Route::resource('patients', PatientController::class);
    Route::resource('maps', ExaminationMapController::class);
});
