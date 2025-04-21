<?php

use App\Http\Controllers\Dashboard\Api\ExaminationMapController;
use App\Http\Controllers\Dashboard\Api\ImageController;
use App\Http\Controllers\Dashboard\Api\MedicationController;
use App\Http\Controllers\Dashboard\Api\StaffController;
use App\Http\Controllers\Dashboard\Api\SurveyController;
use App\Http\Controllers\Dashboard\Api\SurveyResponseController;
use App\Http\Controllers\Dashboard\Api\TransferController;
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
