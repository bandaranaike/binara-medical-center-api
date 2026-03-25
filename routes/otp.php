<?php

use App\Http\Controllers\PhoneVerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('otp')->group(function () {
    Route::post('resend/{token}', [PhoneVerificationController::class, 'resend']);
    Route::post('request', [PhoneVerificationController::class, 'request']);
    Route::post('validate/{token}', [PhoneVerificationController::class, 'validate']);
});
