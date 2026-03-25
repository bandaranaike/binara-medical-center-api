# Architecture Reference

This repository is primarily a Laravel 11 API application with a secondary Inertia/Breeze frontend scaffold still present for web auth/profile screens.

## 1. Runtime shape

### Framework and platform

- Laravel 11
- PHP 8.4 in the current environment
- MariaDB
- Sanctum for API tokens
- Inertia React + Breeze present, but not central to the backend API

### Entry points

- API routes: `routes/api.php`
- Web routes: `routes/web.php`
- Console routes: `routes/console.php`
- Bootstrap and middleware registration: `bootstrap/app.php`

## 2. Request architecture

### API routing structure

`routes/api.php` is the main product map.

High-level route layers:

1. public/session helper endpoints under Sanctum
2. large `verify.apikey` route group
3. nested authenticated routes
4. public booking/patient endpoints that still require API-key access
5. dedicated machine-authenticated `/api/public/*` routes for the Electron app
6. role-gated CRUD resources
7. included `routes/admin.php`
8. included `routes/otp.php`

### Middleware stack

Important aliases from `bootstrap/app.php`:

- `verify.apikey`
- `public.app.token`
- `role`
- `ensure.doctor`
- `ensure.patient`
- `verified`

Cross-cutting access rules:

- API routes are stateful-aware through Sanctum middleware
- almost all API usage expects trusted external clients via API key + referer
- route-level role checks enforce staff capabilities
- Electron desktop routes add a separate app-token middleware and do not depend on user login

## 3. Code organization

### Controllers

`app/Http/Controllers` contains most application behavior.

Patterns used:

- resource-style CRUD controllers
- custom business-flow controllers
- traits mixed into controllers for shared logic

Representative business controllers:

- `BillController`
- `BookingController`
- `DoctorAvailabilityController`
- `SaleController`
- `PatientAuthController`
- `ReportController`
- `PublicApi\PublicPatientController`
- `PublicApi\PublicDoctorController`
- `PublicApi\PublicBillController`

### Shared controller traits

The codebase uses traits as a service layer substitute for some domains.

Important traits:

- `CrudTrait`
  - generic index/store/show/update/destroy behavior
- `BillTrait`
  - bill helper logic
- `BillItemsTrait`
  - bill item insertion/manipulation
- `DailyPatientQueueTrait`
  - queue number generation
- `DoctorAvailabilityTrait`
  - seat adjustments during booking
- `SystemPriceCalculator`
  - pricing split logic
- `PrintingDataProcess`
  - bill print payload creation
- `StockTrait`
  - stock deduction/restoration and temporary sale handling
- `OTPManager`
  - phone verification helpers

Implication for future work:

- before adding new logic, check whether the behavior already lives in a trait
- controller behavior is often distributed across multiple traits rather than one class

### Services

The service layer is light but important.

- `DoctorScheduleService`
  - generates `doctor_availabilities` from recurring `doctor_schedules`
- dropdown strategy services
  - provide lookup options for UI/API consumers
- `DialogESMSService`
  - external SMS integration point

### Models

Models mainly hold relationships and fillable state.

Key aggregate roots:

- `User`
- `Patient`
- `Doctor`
- `Bill`
- `Stock`
- `Sale`
- `DoctorSchedule`
- `DoctorAvailability`
- `TrustedSite`
- `PublicAppToken`

### Resources

`app/Http/Resources` contains API resource transformers for response shaping, especially for:

- bills
- doctors
- patients
- dropdowns
- pharmacy-related responses

### Requests

`app/Http/Requests` is heavily used for validation.

Implication:

- most endpoint contracts should be understood from the request classes first, then the controller

## 4. Domain architecture

### Booking and queueing

- public booking reads from generated doctor availability
- appointment creation adjusts seat inventory
- bill + bill items + queue entry are created together

### Public desktop API

- `/api/public/*` is a separate machine-to-machine surface for the Electron app
- authentication is two-layered:
  - trusted site validation
  - bearer token validation
- no interactive staff login is required
- the token model is app-scoped, not user-scoped

### Billing

- bill is the core transaction
- bill items represent service components
- status transitions drive work between doctor, pharmacy, and reception

### Pharmacy

- stock is batch-based
- sales consume stock across multiple stock rows
- `temporary_sales` acts as the stock-allocation ledger
- patient medicine history is linked back to both bill and sale

### Reporting

- report endpoints query live transactional tables directly with query builder aggregates

## 5. Events and listeners

The codebase uses event/listener pairs for billing and stock side effects.

Observed events:

- `NewBillCreated`
- `AddedDrugForBill`
- `RemovedDrugFromBill`
- `PatientMedicineListUpdated`
- `SendPhoneVerification`

Observed listeners:

- `DeductDrugStockForBill`
- `RestoreDrugStockForBill`
- `SyncMedicineBillItem`
- `SendPhoneVerificationListener`

Implication:

- when billing or sale logic changes, inspect event side effects as well as direct controller code

## 6. Console / operational architecture

Important commands:

- `generate:calendar`
- `app:reset-daily-queue`
- `patient:update-ages`
- `public-api:token`

These support operational upkeep outside normal request handling.

## 7. Frontend presence

The repository includes:

- Inertia React pages
- Breeze auth/profile screens
- Tailwind configuration

However:

- the main business surface is exposed through `/api`
- many frontend assets look secondary to the API product
- do not assume a full SPA flow is the primary source of truth for business logic

## 8. Testing posture

There are PHPUnit feature tests around:

- booking
- billing price/system-price logic
- doctor availability calendar generation
- print data preparation
- public desktop API endpoints

The tests are useful for identifying current behavior, but some areas still rely more on controller/trait inspection than broad integration coverage.

## 9. Practical rules for future changes

- Start with `routes/api.php` to locate the entry point.
- Check the corresponding request class for validation.
- Check the controller for orchestration.
- Check related traits for hidden business logic.
- Check model relationships and events/listeners before changing persistence behavior.
- Check schema and migrations before assuming nullable fields, enums, or soft deletes.
