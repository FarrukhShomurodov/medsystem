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
use App\Http\Controllers\Dashboard\DoctorController;
use App\Http\Controllers\Dashboard\ExaminationMapController;
use App\Http\Controllers\Dashboard\InstitutionController;
use App\Http\Controllers\Dashboard\LaboratoryController;
use App\Http\Controllers\Dashboard\MedicalDeviceController;
use App\Http\Controllers\Dashboard\MedicalEquipmentController;
use App\Http\Controllers\Dashboard\MedicationController;
use App\Http\Controllers\Dashboard\PatientController;
use App\Http\Controllers\Dashboard\ProcedureController;
use App\Http\Controllers\Dashboard\RegionController;
use App\Http\Controllers\Dashboard\ServiceCategoryController;
use App\Http\Controllers\Dashboard\ServiceMethodController;
use App\Http\Controllers\Dashboard\SpecialtyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('dashboard.login');
Route::post('admin/login', [AdminAuthController::class, 'login']);

Route::prefix('dashboard')->middleware('auth:admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::get('', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('admins', AdminController::class);

    Route::resource('specialties', SpecialtyController::class);
    Route::resource('doctors', DoctorController::class);
    Route::resource('institutions', InstitutionController::class);

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
    Route::prefix('service')->group(function () {
        Route::resource('categories', ServiceCategoryController::class);
        Route::resource('procedures', ProcedureController::class);
        Route::resource('methods', ServiceMethodController::class)->names('service.methods');
    });

    Route::resource('laboratories', LaboratoryController::class);
    Route::resource('methods', AnalyzeMethodController::class);
    Route::resource('equipments', MedicalEquipmentController::class);
    Route::resource('types', AnalyzeTypeController::class);

    Route::resource('regions', RegionController::class);
    Route::resource('cities', CityController::class);
    Route::resource('allegories', AllegoryController::class);
    Route::resource('diseases', ChronicDiseaseController::class);
    Route::resource('devices', MedicalDeviceController::class);

    Route::resource('patients', PatientController::class);
    Route::resource('maps', ExaminationMapController::class);
});


Route::post('test', function (Request $request) {

    dd($request);
})->name('test');
