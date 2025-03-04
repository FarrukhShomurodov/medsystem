<?php

use App\Http\Controllers\Dashboard\Api\ExaminationMapController;
use App\Http\Controllers\Dashboard\Api\ImageController;
use App\Http\Controllers\Dashboard\Api\TransferController;
use Illuminate\Support\Facades\Route;

// Delete image
Route::delete('/delete/image/{folderName}/{fileName}', [ImageController::class, 'deletePhoto']);


// Examination maps
Route::get('examination-map-by-patient/{patient}', [ExaminationMapController::class, 'mapByPatient']);


// transfers
Route::patch('/transfers/{transfer}/update-status', [TransferController::class, 'updateStatus']);
