<?php

use App\Http\Controllers\PublicApi\PublicBillController;
use App\Http\Controllers\PublicApi\PublicDoctorController;
use App\Http\Controllers\PublicApi\PublicPatientController;
use Illuminate\Support\Facades\Route;

Route::get('patients/search', [PublicPatientController::class, 'search']);
Route::post('patients', [PublicPatientController::class, 'store']);
Route::put('patients/{patient}', [PublicPatientController::class, 'update']);
Route::post('patients/upsert', [PublicPatientController::class, 'upsert']);
Route::get('doctors', [PublicDoctorController::class, 'index']);
Route::post('bills', [PublicBillController::class, 'store']);
