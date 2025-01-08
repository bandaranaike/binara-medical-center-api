<?php

use App\Http\Controllers\Admin\ServiceAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::resource('services', ServiceAdminController::class);
});
