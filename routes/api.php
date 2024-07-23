<?php


use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorsChannelingFeeController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientsHistoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecialtyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('doctor-channeling-fees', [DoctorsChannelingFeeController::class, 'store']);
Route::get('doctor-channeling-fees', [DoctorsChannelingFeeController::class, 'index']);
Route::apiResource('services', ServiceController::class);
Route::apiResource('doctors', DoctorController::class);
Route::apiResource('allergies', AllergyController::class);
Route::apiResource('diseases', DiseaseController::class);
Route::apiResource('patients', PatientController::class);
Route::apiResource('bills', BillController::class)->except(['destroy']);
Route::apiResource('specialties', SpecialtyController::class);
Route::apiResource('hospitals', HospitalController::class);
Route::apiResource('patients-histories', PatientsHistoryController::class);

Route::post('check-email', [AuthController::class, 'checkEmail']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/dropdown/{table}', [DropdownController::class, 'index']);

Route::get('/patients/get-by-phone/{telephone}', [PatientController::class, 'getPatientDataByTelephone']);

