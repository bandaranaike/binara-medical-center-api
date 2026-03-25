# Database Schema Reference

Database engine: MariaDB

The schema is centered around patient bookings, billing, doctor scheduling, and pharmacy stock/sales.

## Core business domains

### Identity and access

- `users`
  - staff and patient login accounts
  - linked to `roles`
  - Sanctum tokens stored in `personal_access_tokens`
- `roles`
  - role records keyed by values such as `admin`, `doctor`, `reception`, `patient`, `pharmacy`, `pharmacy_admin`, `nurse`
- `trusted_sites`
  - allowed `domain` + `api_key` pairs for the `X-API-KEY` / `Referer` gate
- `public_app_tokens`
  - bearer tokens for non-user application clients such as the Electron desktop app
  - each token belongs to a trusted site
  - stores hashed token value, abilities, expiry, revocation, and last-used timestamp
- `phone_verifications`
  - OTP verification records for phone-based flows

### Patient care

- `patients`
  - patient profile record, optionally linked to `users`
  - soft deletes enabled
- `allergies`
- `diseases`
- `allergy_patient`
  - many-to-many pivot: patients <-> allergies
- `disease_patient`
  - many-to-many pivot: patients <-> diseases
- `patients_histories`
  - doctor-authored free-text notes for a patient
- `patient_medicine_histories`
  - medicine instructions/history tied to patient, doctor, bill, sale, and medication frequency
- `medication_frequencies`
  - dosage frequency lookup

### Doctors and availability

- `hospitals`
- `specialties`
- `doctors`
  - belongs to hospital, specialty, and user
  - supports `doctor_type` values like `dental`, `opd`, `specialist`, `treatment`
- `doctors_channeling_fees`
  - doctor-level fee record
- `doctor_schedules`
  - recurring templates by weekday/time/seats/recurring/status
- `doctor_availabilities`
  - generated calendar slots with `seats` and `available_seats`
  - optionally tied back to `doctor_schedule_id`

### Billing and queues

- `services`
  - billable service catalog
  - fields include `key`, `bill_price`, `system_price`, `separate_items`, `is_percentage`
- `bills`
  - central encounter / booking / bill record
  - links patient and doctor
  - stores workflow status, payment data, appointment type, date, shift
  - soft deletes enabled
- `bill_items`
  - line items for a bill, each linked to a `service`
- `daily_patient_queues`
  - queue number and order number per bill/doctor/day

### Pharmacy and inventory

- `categories`
- `drugs`
  - belongs to category
- `brands`
  - belongs to drug
- `suppliers`
- `stocks`
  - inventory batches by brand and supplier
  - stores unit price, cost, batch number, expiry, current quantity
- `sales`
  - medicine sale lines tied to a bill and brand
- `temporary_sales`
  - stock deduction ledger per sale and stock batch
- `medicines`
  - separate medicine table exists, but the main pharmacy flow appears to use `drugs`, `brands`, `stocks`, and `sales`

### Communication / support

- `contacts`
  - public contact submissions with status tracking

## Key relationships

- `users.role_id -> roles.id`
- `patients.user_id -> users.id`
- `public_app_tokens.trusted_site_id -> trusted_sites.id`
- `doctors.user_id -> users.id`
- `doctors.hospital_id -> hospitals.id`
- `doctors.specialty_id -> specialties.id`
- `doctors_channeling_fees.doctor_id -> doctors.id`
- `doctor_schedules.doctor_id -> doctors.id`
- `doctor_availabilities.doctor_id -> doctors.id`
- `doctor_availabilities.doctor_schedule_id -> doctor_schedules.id`
- `bills.patient_id -> patients.id`
- `bills.doctor_id -> doctors.id`
- `bill_items.bill_id -> bills.id`
- `bill_items.service_id -> services.id`
- `daily_patient_queues.bill_id -> bills.id`
- `daily_patient_queues.doctor_id -> doctors.id`
- `patients_histories.patient_id -> patients.id`
- `patients_histories.doctor_id -> doctors.id`
- `patient_medicine_histories.patient_id -> patients.id`
- `patient_medicine_histories.doctor_id -> doctors.id`
- `patient_medicine_histories.bill_id -> bills.id`
- `patient_medicine_histories.sale_id -> sales.id`
- `patient_medicine_histories.medication_frequency_id -> medication_frequencies.id`
- `drugs.category_id -> categories.id`
- `brands.drug_id -> drugs.id`
- `stocks.brand_id -> brands.id`
- `stocks.supplier_id -> suppliers.id`
- `sales.brand_id -> brands.id`
- `sales.bill_id -> bills.id`
- `temporary_sales.stock_id -> stocks.id`
- `temporary_sales.sale_id -> sales.id`
- `temporary_sales.bill_id -> bills.id`

## Important enums / state columns

### `bills.status`

- `booked`
- `doctor`
- `done`
- `pharmacy`
- `reception`
- `treatment`

### `bills.payment_type`

- `cash`
- `card`
- `online`

### `bills.payment_status`

- `pending`
- `paid`
- `cancelled`
- `refunded`

### `doctors.doctor_type`

- `dental`
- `opd`
- `specialist`
- `treatment`

### `doctor_availabilities.status`

- `active`
- `canceled`

### `doctor_schedules.status`

- `active`
- `inactive`

## Practical schema notes

- `/api/public/*` routes are authenticated by both `trusted_sites` and `public_app_tokens`.
- `public_app_tokens` are app-level machine credentials, not staff or patient login tokens.
- Booking creates a `bill`, related `bill_items`, and a `daily_patient_queues` row.
- Pharmacy sales decrement `stocks` and persist per-batch deductions in `temporary_sales`.
- Removing or changing a sale restores stock first, then re-applies deductions.
- A patient can exist without a fully populated user account, but web/patient auth tries to keep `users` and `patients` linked.
- `bills` and `patients` are soft-deleted, so reports or queries must decide whether to include deleted records.
