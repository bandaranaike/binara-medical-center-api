<?php

use App\Http\Controllers\PublicApi\PublicBillController;
use App\Http\Controllers\PublicApi\PublicBookingController;
use App\Http\Controllers\PublicApi\PublicDoctorController;
use App\Http\Controllers\PublicApi\PublicPatientController;
use App\Http\Controllers\PublicApi\PublicReportController;
use App\Http\Controllers\PublicApi\PublicServiceController;
use Illuminate\Support\Facades\Route;

Route::get('patients/search', [PublicPatientController::class, 'search']);
Route::post('patients', [PublicPatientController::class, 'store']);
Route::put('patients/{patient}', [PublicPatientController::class, 'update']);
Route::post('patients/upsert', [PublicPatientController::class, 'upsert']);
Route::get('services/search', [PublicServiceController::class, 'search']);
Route::get('doctors', [PublicDoctorController::class, 'index'])->name('public.doctors.index');
Route::get('doctors/by-date', [PublicDoctorController::class, 'index'])->name('public.doctors.by-date');
Route::get('doctors/{doctor}/billing-config', [PublicDoctorController::class, 'billingConfig']);
Route::post('bills', [PublicBillController::class, 'store']);
Route::get('reports/day-summary', [PublicReportController::class, 'daySummary']);
Route::post('bookings/make-appointment', [PublicBookingController::class, 'makeAppointment']);
Route::get('bookings', [PublicBookingController::class, 'index']);
Route::get('bookings/{booking}', [PublicBookingController::class, 'show']);
Route::put('bookings/{booking}', [PublicBookingController::class, 'update']);
Route::delete('bookings/{booking}', [PublicBookingController::class, 'destroy']);
Route::post('bookings/{booking}/proceed-to-payment', [PublicBookingController::class, 'proceedToPayment']);
