# Functionality Reference

This document summarizes what the API does at a business level and which files currently implement the behavior.

## 1. Access model

Most `/api/*` routes are inside a `verify.apikey` group.

Required request pattern for most endpoints:

- `X-API-KEY` header
- `Referer` header
- matching row in `trusted_sites`

Private endpoints add one or more of:

- `auth:sanctum`
- `auth`
- `role:*`
- `ensure.doctor`
- `ensure.patient`

Separate machine-authenticated desktop endpoints now also exist under `/api/public/*` and use:

- `verify.apikey`
- `public.app.token`

Main files:

- `bootstrap/app.php`
- `app/Http/Middleware/VerifyApiKey.php`
- `app/Http/Middleware/AuthenticatePublicAppToken.php`
- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Middleware/EnsureDoctor.php`
- `app/Http/Middleware/EnsurePatient.php`
- `routes/api.php`

## 2. Authentication flows

### Staff-style auth

- `POST /api/register`
- `POST /api/login`
- `GET /api/check-user`
- `GET /api/check-user-session`

Behavior:

- creates or authenticates a `users` record
- issues a Sanctum token
- returns role and basic user identity

Main file:

- `app/Http/Controllers/AuthController.php`

### Patient auth

- patient registration
- patient login via email or phone
- patient logout
- password reset endpoints
- profile update

Behavior:

- creates a `users` record with patient role
- auto-creates or links a `patients` record
- issues Sanctum tokens

Main file:

- `app/Http/Controllers/PatientAuthController.php`

### OTP / phone verification

- request OTP
- resend OTP
- validate OTP

Used by booking flows that require verified phone numbers.

Main files:

- `routes/otp.php`
- `app/Http/Controllers/PhoneVerificationController.php`
- `app/Http/Controllers/Traits/OTPManager.php`

### Public desktop app token auth

The Electron integration now has a dedicated machine-auth path:

- trusted site validation via `X-API-KEY` + `Referer`
- bearer token validation via `Authorization: Bearer <public-app-token>`
- no staff login
- no user session
- no CSRF flow

Current public desktop endpoints:

- `GET /api/public/patients/search`
- `POST /api/public/patients`
- `PUT /api/public/patients/{id}`
- `POST /api/public/patients/upsert`
- `GET /api/public/doctors`
- `POST /api/public/bills`

Main files:

- `app/Models/PublicAppToken.php`
- `app/Http/Middleware/AuthenticatePublicAppToken.php`
- `routes/public.php`
- `app/Console/Commands/CreatePublicApiToken.php`

Operational note:

- new public bearer tokens are issued with `php artisan public-api:token {trusted_site} {name}`

## 3. Booking and appointments

Public booking endpoints let external clients:

- search doctors with availability
- list upcoming availability
- fetch doctor dates
- book an appointment
- view patient booking history for web users

Main behavior of appointment creation:

1. validate booking request
2. ensure phone is verified
3. reduce available seats for the doctor/date
4. create or reuse patient / user records
5. prevent duplicate same-day booking for same doctor
6. resolve service pricing
7. create the bill
8. create default bill items
9. create daily queue entry
10. return booking reference and queue number

Main files:

- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/DoctorAvailabilityController.php`
- `app/Http/Controllers/Traits/DoctorAvailabilityTrait.php`
- `app/Http/Controllers/Traits/BillTrait.php`
- `app/Http/Controllers/Traits/BillItemsTrait.php`
- `app/Http/Controllers/Traits/DailyPatientQueueTrait.php`
- `app/Http/Controllers/Traits/SystemPriceCalculator.php`
- `app/Services/DoctorScheduleService.php`

## 4. Doctor schedule and availability management

Internal admin/reception flows can CRUD doctor schedules and availabilities.

Availability generation supports:

- recurring schedules
- month generation
- optional override of existing rows
- linking generated rows back to schedule templates

Relevant behavior:

- `doctor_schedules` define templates
- `doctor_availabilities` are the bookable calendar rows
- public search endpoints read from `doctor_availabilities`
- booking decrements `available_seats`

Main files:

- `app/Http/Controllers/DoctorScheduleController.php`
- `app/Http/Controllers/DoctorAvailabilityController.php`
- `app/Services/DoctorScheduleService.php`
- `app/Console/Commands/GenerateDoctorsAvailabilityCalendar.php`

## 5. Billing workflow

Billing is the center of the app.

### Bill creation

Bills can be created directly or via booking. A bill stores:

- patient
- doctor
- date
- appointment type
- status
- bill amount
- system amount
- payment metadata

### Bill lifecycle

Observed statuses:

- `booked`
- `doctor`
- `pharmacy`
- `reception`
- `done`
- `treatment`

Typical flow:

1. booking or reception creates the bill
2. doctor processes pending doctor bills
3. pharmacy handles medicine-related steps
4. reception finalizes or collects
5. bill can be marked done, which also updates payment state

Special behaviors:

- future booking promoted to `doctor` gets its date reset to today
- moving to `pharmacy` inserts a medicine bill item if needed
- duplicate same-day booking is detected and returned as a warning
- queue data is attached to booking and reception views

Main files:

- `app/Http/Controllers/BillController.php`
- `app/Http/Controllers/BillCrudController.php`
- `app/Http/Controllers/BillItemController.php`
- `app/Http/Controllers/PublicApi/PublicBillController.php`
- `app/Http/Controllers/Traits/PrintingDataProcess.php`
- `app/Http/Controllers/Traits/SystemPriceCalculator.php`

## 6. Pharmacy, stock, and medicine history

This is a strong domain in the codebase.

Main capabilities:

- manage categories, drugs, brands, suppliers, stocks
- create sales tied to bills
- deduct stock in FIFO-by-expiry order
- restore stock on sale removal or quantity change
- write patient medicine history against sales and bills

Observed stock algorithm:

- load available stock batches for a brand
- verify sufficient total quantity
- create a `sales` row first
- deduct from earliest-expiring stock first
- persist deductions in `temporary_sales`
- recalculate sale total price from unit prices
- fire an event to refresh medicine-related bill totals

Main files:

- `app/Http/Controllers/SaleController.php`
- `app/Http/Controllers/StockController.php`
- `app/Http/Controllers/DrugController.php`
- `app/Http/Controllers/BrandController.php`
- `app/Http/Controllers/SupplierController.php`
- `app/Http/Controllers/PatientsMedicineHistoryController.php`
- `app/Http/Controllers/Traits/StockTrait.php`

## 7. Patient medical records

The API maintains patient clinical context beyond billing:

- allergies
- diseases
- free-text history notes
- medicine history

Typical doctor actions:

- add/remove allergy
- add/remove disease
- add history notes
- add prescribed medicine entries
- read history scoped to a patient or bill

Main files:

- `app/Http/Controllers/PatientsAllergyController.php`
- `app/Http/Controllers/PatientsDiseaseController.php`
- `app/Http/Controllers/PatientsHistoryController.php`
- `app/Http/Controllers/PatientsMedicineHistoryController.php`
- `app/Http/Controllers/PublicApi/PublicPatientController.php`

## 8. Public desktop billing API

This is a separate API surface for the Electron medical-center application.

Main capabilities:

- search patients by name or telephone
- create patients
- update patients
- upsert patients by telephone
- list doctors for billing selection
- create bills without interactive staff login

Important behavior:

- public app tokens are bound to trusted sites
- the desktop app must send both trusted-site headers and bearer token
- bill creation still creates queue entries
- when a service-type maps to an existing default service, a default bill item is inserted

Main files:

- `app/Http/Controllers/PublicApi/PublicPatientController.php`
- `app/Http/Controllers/PublicApi/PublicDoctorController.php`
- `app/Http/Controllers/PublicApi/PublicBillController.php`
- `app/Http/Requests/PublicApi/*`
- `app/Http/Middleware/AuthenticatePublicAppToken.php`
- `routes/public.php`

## 9. Master-data CRUD modules

Many resources share generic CRUD behavior through `CrudTrait`.

Examples:

- allergies
- brands
- categories
- diseases
- doctors
- doctor availabilities
- doctor schedules
- drugs
- hospitals
- roles
- services
- specialties
- stocks
- suppliers
- trusted sites
- users

Shared list features:

- pagination
- simple field search
- relation search using `relation:field`
- multi-field sort via query string
- comma-separated delete ids

Main file:

- `app/Http/Controllers/Traits/CrudTrait.php`

## 10. Reports

Admin report endpoints expose operational and revenue summaries.

Current report types:

- bill status summary
- new vs updated patient counts
- visited doctors count
- revenue by doctor
- total revenue
- service cost report
- services with positive system amount

Main file:

- `app/Http/Controllers/ReportController.php`

## 11. Miscellaneous modules

- `ContactController`
  - accepts public contact submissions
- `DropdownController`
  - provides generic dropdown data for many tables using strategy classes in `app/Services`
- `TrustedSiteController`
  - manages domains/API keys allowed through the API-key gate

## 12. Operational commands

- `generate:calendar`
  - bulk-generate doctor availability rows
- `app:reset-daily-queue`
  - clears prior queue data
- `patient:update-ages`
  - recalculates patient ages
- `public-api:token`
  - issues bearer tokens for machine clients such as the Electron desktop app

Main files:

- `app/Console/Commands/GenerateDoctorsAvailabilityCalendar.php`
- `app/Console/Commands/ResetDailyQueue.php`
- `app/Console/Commands/UpdatePatientAges.php`
- `app/Console/Commands/CreatePublicApiToken.php`
