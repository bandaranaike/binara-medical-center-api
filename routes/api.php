<?php


use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorsChannelingFeeController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\PatientAllergyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientsHistoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecialtyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::delete('patients/remove-allergy/{allergyId}', [PatientAllergyController::class, 'removeAllergy']);

Route::get('bills/get-next-bill-number', [BillController::class, "getNextBillNumber"]);
Route::get('bills/pending', [BillController::class, 'getPendingBills']);
Route::get('doctor-channeling-fees/get-fee/{id}', [DoctorsChannelingFeeController::class, "showFee"]);
Route::get('dropdown/{table}', [DropdownController::class, 'index']);
Route::get('patients/get-by-phone/{telephone}', [PatientController::class, 'getPatientDataByTelephone']);
Route::get('user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('check-email', [AuthController::class, 'checkEmail']);
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('patients/add-allergy', [PatientAllergyController::class, 'addAllergy']);

Route::apiResource('allergies', AllergyController::class);
Route::apiResource('bills', BillController::class)->except(['destroy']);
Route::apiResource('diseases', DiseaseController::class);
Route::apiResource('doctors', DoctorController::class);
Route::apiResource('doctor-channeling-fees', DoctorsChannelingFeeController::class);
Route::apiResource('hospitals', HospitalController::class);
Route::apiResource('patients', PatientController::class);
Route::apiResource('patients-histories', PatientsHistoryController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('specialties', SpecialtyController::class);



