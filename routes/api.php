<?php

use App\Http\Controllers\AllergyController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillCrudController;
use App\Http\Controllers\BillItemController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorsChannelingFeeController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DropdownController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\PatientAuthController;
use App\Http\Controllers\PatientsAllergyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientsDiseaseController;
use App\Http\Controllers\PatientsHistoryController;
use App\Http\Controllers\PatientsMedicineHistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TrustedSiteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/check-user', function (Request $request) {
    return $request->user();
});
Route::middleware(['auth:sanctum'])->get('/check-user-session', [AuthController::class, 'checkUserSession']);;

Route::middleware(['verify.apikey'])->group(function () {


    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);

    Route::middleware(['auth:sanctum', 'auth'])->group(function () {
        Route::delete('patients/remove-allergy/{allergyId}', [PatientsAllergyController::class, 'removeAllergy'])
            ->middleware('role:doctor');
        Route::delete('patients/remove-disease/{diseaseId}', [PatientsDiseaseController::class, 'removeDisease'])
            ->middleware('role:doctor');

        Route::get('bills/{billId}/sales', [SaleController::class, "getDrugSalesForBill"])
            ->middleware('role:reception,pharmacy,pharmacy_admin,admin,doctor');

        Route::get('bills/{billId}/bill-items', [BillItemController::class, "getBillItemsForBill"])
            ->middleware('role:reception,pharmacy,pharmacy_admin,admin,doctor');
        Route::get('bills/bookings/{time?}', [BillController::class, "bookings"])->middleware('role:reception,admin');
        Route::get('bills/pending/doctor', [BillController::class, 'getPendingBillsForDoctor'])
            ->middleware(['role:doctor', 'ensure.doctor']);
        Route::get('bills/pending/pharmacy', [BillController::class, 'getPendingBillsForPharmacy'])
            ->middleware(['role:pharmacy,pharmacy_admin,doctor']);
        Route::get('bills/pending/reception', [BillController::class, 'getPendingBillsForReception'])
            ->middleware('role:reception,admin');
        Route::get('doctors/patient/{patientId}/histories', [PatientsHistoryController::class, 'getPatientHistory'])
            ->middleware(['role:doctor', 'ensure.doctor']);
        Route::get('doctors/patient/{patientId}/medicine-histories', [PatientsMedicineHistoryController::class, 'getMedicineHistories'])
            ->middleware(['role:pharmacy,doctor,admin', 'ensure.doctor']);
        Route::get('doctors/patient/bill/{billId}/medicine-histories', [PatientsMedicineHistoryController::class, 'getHistoryForABill'])
            ->middleware(['role:doctor,pharmacy']);
        Route::get('doctor-channeling-fees/get-fee/{id}', [DoctorsChannelingFeeController::class, "getFee"])
            ->middleware('role:reception');
        Route::get('dropdown/{table}', [DropdownController::class, 'index']);
        Route::get('drugs/stock-sale-data', [DrugController::class, 'getDrugStockSaleData'])
            ->middleware('role:pharmacy_admin,admin');
        Route::get('patients/search', [PatientController::class, 'search'])->middleware('role:reception');

        Route::patch('sales/update-quantity', [SaleController::class, 'changeStockQuantity'])
            ->middleware('role:pharmacy_admin,admin,doctor,pharmacy');
        Route::put('/sales/update-number-of-days', [SaleController::class, 'updateNumberOfDays']);


        Route::post('logout', [PatientAuthController::class, 'destroy']);
        Route::post('patients/add-allergy', [PatientsAllergyController::class, 'store'])
            ->middleware('role:doctor');
        Route::post('patients/add-disease', [PatientsDiseaseController::class, 'store'])
            ->middleware('role:doctor');
        Route::post('patients/add-history', [PatientsHistoryController::class, 'store'])
            ->middleware(['role:doctor', 'ensure.doctor']);
        Route::post('patients/add-medicine', [PatientsMedicineHistoryController::class, 'store'])
            ->middleware(['role:doctor', 'ensure.doctor']);
        Route::post('users/create-from-doctor', [UserController::class, 'createUserForDoctor'])
            ->middleware(['role:admin']);

        Route::put('bills/{billId}/send-to-reception', [BillController::class, 'sendBillToReception']);
        Route::put('bills/{billId}/status', [BillController::class, 'updateStatus']);
        Route::put('bills/{billId}/change-temp-status', [BillController::class, 'changeTempBillStatus'])->middleware('role:reception,nurse');

        Route::prefix('reports')->middleware(['role:admin'])->group(function () {
            Route::get('', [ReportController::class, 'index']);
            Route::get('service-costs', [ReportController::class, 'serviceCostReport']);
            Route::get('services-with-positive-system-amount', [ReportController::class, 'getServicesWithPositiveSystemAmount']);
        });


    });

    /**
     * +--------------------------------+
     * |  Public AIPs                   |
     * +--------------------------------+
     */

    Route::delete('patient/delete-patient/{patient}', [PatientController::class, 'destroy'])->middleware('auth:sanctum');

    Route::get('bookings/doctors/list', [BookingController::class, 'getDoctorsList']);
    Route::get('bookings/patients/history', [BookingController::class, 'getPatientsHistoryForWeb'])
        ->middleware(['ensure.patient', 'auth:sanctum']);

    Route::get('doctor-availabilities', [DoctorAvailabilityController::class, 'getAvailability']);
    Route::get('doctor-availabilities/get-today-doctors', [DoctorAvailabilityController::class, 'getTodayAvailableDoctorsForWeb']);
    Route::get('doctor-availabilities/search-doctor', [DoctorAvailabilityController::class, 'searchDoctor']);
    Route::get('doctor-availabilities/doctor/{doctorId}/get-dates', [DoctorAvailabilityController::class, 'getDatesForDoctor']);
    Route::get('doctor-availabilities/search-booking-doctors', [DoctorAvailabilityController::class, 'searchDoctorsForWebBooking']);

    Route::get('patient/user', [PatientController::class, 'loggedUserDetailsForWeb'])
        ->middleware(['ensure.patient', 'auth:sanctum']);

    Route::get('patient/user-patients', [PatientController::class, 'usersPatientsListForWeb'])
        ->middleware(['ensure.patient', 'auth:sanctum']);

    Route::post('bookings/make-appointment', [BookingController::class, 'makeAppointment']);
    Route::post('contacts', [ContactController::class, 'store']);
    Route::post('patient/create-patient', [PatientController::class, 'store'])->middleware('auth:sanctum');
    Route::post('patient/change-password', [PasswordController::class, 'update'])->middleware('auth:sanctum');
    Route::post('patient/forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::post('patient/login', [PatientAuthController::class, 'login']);
    Route::post('patient/logout', [PatientAuthController::class, 'destroy'])->middleware('auth:sanctum');
    Route::post('patient/register', [PatientAuthController::class, 'register']);
    Route::post('patient/reset-password', [NewPasswordController::class, 'store']);

    Route::put('patient/update-profile', [PatientAuthController::class, 'updateProfile'])->middleware('auth:sanctum');
    Route::put('patient/update-patient/{patient}', [PatientController::class, 'update'])->middleware('auth:sanctum');

    /**
     * +--------------------------------+
     * |  End of Public AIPs            |
     * +--------------------------------+
     */

    Route::apiResource('allergies', AllergyController::class)->middleware(['role:admin']);
    Route::apiResource('bills', BillController::class)->middleware(['role:admin,reception']);
    Route::apiResource('bill-cruds', BillCrudController::class)->middleware(['role:admin,reception']);
    Route::apiResource('bill-items', BillItemController::class)->middleware(['role:admin,pharmacy_admin,pharmacy,reception,doctor']);
    Route::apiResource('brands', BrandController::class)->middleware(['role:admin,pharmacy_admin']);
    Route::apiResource('categories', CategoryController::class)->middleware(['role:admin,pharmacy_admin']);
    Route::apiResource('diseases', DiseaseController::class)->middleware(['role:admin']);
    Route::apiResource('doctors', DoctorController::class)->middleware(['role:admin,reception']);
    Route::apiResource('doctors-availabilities', DoctorAvailabilityController::class)->middleware(['role:admin,reception']);
    Route::apiResource('doctors-schedules', DoctorScheduleController::class)->middleware(['role:admin,reception']);
    Route::apiResource('drugs', DrugController::class)->middleware(['role:admin,pharmacy_admin']);
    Route::apiResource('doctor-channeling-fees', DoctorsChannelingFeeController::class)->middleware(['role:admin']);
    Route::apiResource('hospitals', HospitalController::class)->middleware(['role:admin']);
    Route::apiResource('patients', PatientController::class)->middleware(['role:admin,reception']);
    Route::apiResource('patient-medicine-histories', PatientsMedicineHistoryController::class)
        ->middleware(['role:admin,reception,pharmacy,pharmacy_admin,doctor']);
    Route::apiResource('roles', RoleController::class)->middleware(['role:admin']);
    Route::apiResource('sales', SaleController::class)->middleware(['role:admin,pharmacy_admin,reception,pharmacy,doctor']);
    Route::apiResource('services', ServiceController::class)->middleware(['role:admin']);
    Route::apiResource('specialties', SpecialtyController::class)->middleware(['role:admin']);
    Route::apiResource('stocks', StockController::class)->middleware(['role:admin,pharmacy_admin']);
    Route::apiResource('suppliers', SupplierController::class)->middleware(['role:admin,pharmacy_admin']);
    Route::apiResource('trusted-sites', TrustedSiteController::class)->middleware(['role:admin']);
    Route::apiResource('users', UserController::class)->middleware(['role:admin']);

    require base_path('routes/admin.php');
    require base_path('routes/otp.php');
});


