<?php

use App\Http\Controllers\Dashboard\Api\ExaminationMapController;
use App\Http\Controllers\Dashboard\Api\ImageController;
use App\Http\Controllers\Dashboard\Api\MedicationController;
use App\Http\Controllers\Dashboard\Api\PatientController;
use App\Http\Controllers\Dashboard\Api\ReceptionController;
use App\Http\Controllers\Dashboard\Api\StaffController;
use App\Http\Controllers\Dashboard\Api\SurveyController;
use App\Http\Controllers\Dashboard\Api\SurveyResponseController;
use App\Http\Controllers\Dashboard\Api\TransferController;
use App\Models\Staff;
use Illuminate\Support\Facades\Route;

// Delete image
Route::delete('/delete/image/{folderName}/{fileName}', [ImageController::class, 'deletePhoto']);

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
Route::get('/staff-by-position/{positionId}', [ReceptionController::class, 'staffByPosition']);
Route::post('/receptions', [ReceptionController::class, 'store']);
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
