<?php


use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillItemController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorsChannelingFeeController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\PatientMedicineHistoryController;
use App\Http\Controllers\PatientsAllergyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientsDiseaseController;
use App\Http\Controllers\PatientsHistoryController;
use App\Http\Controllers\PatientsMedicineHistoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecialtyController;
use Illuminate\Support\Facades\Route;


Route::post('check-email', [AuthController::class, 'checkEmail']);
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'auth'])->group(function () {
    Route::delete('patients/remove-allergy/{allergyId}', [PatientsAllergyController::class, 'removeAllergy']);
    Route::delete('patients/remove-disease/{diseaseId}', [PatientsDiseaseController::class, 'removeDisease']);

    Route::get('bills/get-next-bill-number', [BillController::class, "getNextBillNumber"]);
    Route::get('bills/pending', [BillController::class, 'getPendingBills']);
    Route::get('bills/pending-invoices', [BillController::class, 'getPendingInvoices']);
    Route::get('doctors/patient/{patientId}/histories', [PatientsHistoryController::class, 'getPatientHistory']);
    Route::get('doctors/patient/{patientId}/medicine-histories', [PatientsMedicineHistoryController::class, 'getMedicineHistories']);
    Route::get('doctor-channeling-fees/get-fee/{id}', [DoctorsChannelingFeeController::class, "showFee"]);
    Route::get('dropdown/{table}', [DropdownController::class, 'index']);
    Route::get('patients/get-by-phone/{telephone}', [PatientController::class, 'getPatientDataByTelephone']);

    Route::post('bill-items', [BillItemController::class, 'store']);
    Route::post('patients/add-allergy', [PatientsAllergyController::class, 'store']);
    Route::post('patients/add-disease', [PatientsDiseaseController::class, 'store']);
    Route::post('patients/add-history', [PatientsHistoryController::class, 'store']);
    Route::post('patients/add-medicine', [PatientsMedicineHistoryController::class, 'store']);

    Route::put('bill-items/{id}', [BillItemController::class, 'update']);
    Route::put('bills/{billId}/finalize', [BillController::class, 'finalizeBill']);
});

Route::apiResource('allergies', AllergyController::class);
Route::apiResource('bills', BillController::class)->except(['destroy']);
Route::apiResource('diseases', DiseaseController::class);
Route::apiResource('doctors', DoctorController::class);
Route::apiResource('doctor-channeling-fees', DoctorsChannelingFeeController::class);
Route::apiResource('hospitals', HospitalController::class);
Route::apiResource('patients', PatientController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('specialties', SpecialtyController::class);



