<?php

use App\Http\Controllers\Dashboard\Api\DocumentScoreController;
use App\Http\Controllers\Dashboard\Api\ExaminationMapController;
use App\Http\Controllers\Dashboard\Api\FileController;
use App\Http\Controllers\Dashboard\Api\MedicationController;
use App\Http\Controllers\Dashboard\Api\PatientController;
use App\Http\Controllers\Dashboard\Api\PatientHistoryController;
use App\Http\Controllers\Dashboard\Api\PatientServiceController;
use App\Http\Controllers\Dashboard\Api\ReceptionController;
use App\Http\Controllers\Dashboard\Api\ScoreFinishedController;
use App\Http\Controllers\Dashboard\Api\SelectedDiagnosisController;
use App\Http\Controllers\Dashboard\Api\ServiceController;
use App\Http\Controllers\Dashboard\Api\StaffController;
use App\Http\Controllers\Dashboard\Api\SurveyController;
use App\Http\Controllers\Dashboard\Api\SurveyResponseController;
use App\Http\Controllers\Dashboard\Api\TransferController;
use App\Models\Staff;
use Illuminate\Support\Facades\Route;

// Delete image
Route::delete('/delete/file/{folderName}/{fileName}', [FileController::class, 'deleteFile']);
Route::delete('/delete/file/{folderName}/{fileName}', [FileController::class, 'deleteFile']);

// Examination maps
Route::get('examination-map-by-patient/{patient}', [ExaminationMapController::class, 'mapByPatient']);

// transfers
Route::patch('/transfers/{transfer}/update-status', [TransferController::class, 'updateStatus']);

// surveys
Route::get('/surveys/{staff}', [StaffController::class, 'surveys']);
Route::get('/surveys/{survey}/questions', [SurveyController::class, 'getQuestions']);

// surveys response
Route::get('/surveys-response/show/{response}', [SurveyResponseController::class, 'show']);

// medication
Route::get('/medication/show/{medication}', [MedicationController::class, 'show']);
Route::get('/medication/active-substance/{substance}', [MedicationController::class, 'medicationByActiveSubstance']);

// receptions
Route::get('/staff-by-service/{serviceId}', [ReceptionController::class, 'staffByService']);
Route::post('/receptions', [ReceptionController::class, 'store']);
Route::put('/reception-status-update/{reception}', [ReceptionController::class, 'changeStatus']);
Route::get('/receptions/{reception}', [ReceptionController::class, 'show']);

Route::get('/staff-availability-today/{staff}', function (Staff $staff) {
    return response()->json($staff->getTodayAvailability());
});

Route::get('/staff-booked-list-today/{staff}', function (Staff $staff) {
    return response()->json($staff->getBookedTime());
});

Route::get('staff-availability-week/{id}', [StaffController::class, 'availabilityWeek']);

// patients
Route::get('/patients', [PatientController::class, 'get']);
Route::get('/patient/{patient}', [PatientController::class, 'show']);

// patients
Route::get('patient/service/update-status/{service}', [PatientServiceController::class, 'changeStatus']);

Route::post('admin/patient/history/partial', [PatientHistoryController::class, 'partial'])
    ->name('admin.patients.history.partial');

// services
Route::get('service/by-type/{type}', [ServiceController::class, 'getByType']);

// selected diagnosis
Route::post('/selected-diagnosis', [SelectedDiagnosisController::class, 'store']);

// document score
Route::get('/document-scores/{document}/{score}', [DocumentScoreController::class, 'getActions']);

// document finish action
Route::post('/document/finish/action', [ScoreFinishedController::class, 'finish']);
