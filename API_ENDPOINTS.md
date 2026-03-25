# API Endpoint Guide

This document was generated from `routes.json` and the Laravel controllers/requests in this project. It is written for another application, especially an AI agent client.

## Base Rules

- Base path: `/api`
- Content type: `application/json`
- Most API routes require `X-API-KEY` and a `Referer` header.
- The `Referer` host must match a `trusted_sites.domain` record with the same API key.
- Private staff/patient routes also require `Authorization: Bearer <sanctum_token>`.

### Required headers for most routes

```http
Content-Type: application/json
Accept: application/json
X-API-KEY: <trusted-site-api-key>
Referer: https://your-allowed-app.example
Authorization: Bearer <token>   # only for Sanctum-protected routes
```

## Authentication Model

### API key gate

Almost every `/api/*` route inside the main API group uses `VerifyApiKey`.

- Header: `X-API-KEY`
- Header: `Referer`
- Validation rule: `trusted_sites.domain == referer host` and `trusted_sites.api_key == X-API-KEY`

### Sanctum token gate

Used for logged-in user/patient/staff routes.

- Header: `Authorization: Bearer <token>`

### Role gate

Many private routes use `role:<roles...>`.

Common roles seen in routes:

- `admin`
- `reception`
- `doctor`
- `pharmacy`
- `pharmacy_admin`
- `nurse`
- `patient`

## Common Patterns

### Trait-based CRUD resources

These resources share the same controller behavior through `CrudTrait`:

- `allergies`
- `bill-cruds`
- `brands`
- `categories`
- `diseases`
- `doctors`
- `doctors-availabilities`
- `drugs`
- `roles`
- `services`
- `specialties`
- `stocks`
- `suppliers`
- `trusted-sites`
- `users`

Common endpoints:

- `GET /api/<resource>`
- `POST /api/<resource>`
- `GET /api/<resource>/{id}`
- `PUT|PATCH /api/<resource>/{id}`
- `DELETE /api/<resource>/{id}`

Common list query parameters:

- `searchField`
- `searchValue`
- `sort[]=field`
- `sort[]=field:desc`

Common list response shape:

```json
{
  "data": [...],
  "last_page": 1
}
```

Common delete behavior for trait-based resources:

- `DELETE /api/<resource>/1,2,3` is supported in many of these controllers because IDs are split on commas before delete.

## Public Authentication Endpoints

These require API key headers, but not a Sanctum token.

### Staff-style login/register

#### `POST /api/login`

Body:

```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

Response:

```json
{
  "message": "Logged in successfully",
  "token": "<sanctum-token>",
  "role": "reception",
  "name": "User Name"
}
```

#### `POST /api/register`

Body:

```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "secret",
  "password_confirmation": "secret",
  "role": "reception"
}
```

Allowed `role` values in this controller:

- `reception`
- `patient`
- `pharmacy`
- `doctor`
- `nurse`

Response: same token response as `/api/login`

#### `POST /api/forgot-password`

Body:

```json
{
  "email": "user@example.com"
}
```

#### `POST /api/reset-password`

Body:

```json
{
  "token": "<reset-token>",
  "email": "user@example.com",
  "password": "new-secret",
  "password_confirmation": "new-secret"
}
```

### Patient auth

#### `POST /api/patient/register`

Body:

```json
{
  "name": "Patient User",
  "email": "patient@example.com",
  "phone": "+94770000000",
  "password": "secret",
  "password_confirmation": "secret"
}
```

Notes:

- At least one of `email` or `phone` is required.
- On success this auto-creates or links a `patients` record and returns a token.

#### `POST /api/patient/login`

Body:

```json
{
  "username": "patient@example.com",
  "password": "secret",
  "remember": true
}
```

Notes:

- `username` can be email or phone.

Response:

```json
{
  "name": "Patient User",
  "id": "<user-uuid>",
  "email": "patient@example.com",
  "phone": "+94770000000",
  "message": "Logged in successfully",
  "token": "<sanctum-token>"
}
```

#### `POST /api/patient/forgot-password`

Body:

```json
{
  "email": "patient@example.com"
}
```

#### `POST /api/patient/reset-password`

Same body as `/api/reset-password`.

### OTP endpoints

#### `POST /api/otp/request`

Body:

```json
{
  "phone_number": "+94770000000"
}
```

Response:

```json
{
  "message": "OTP generated successfully",
  "token": "<verification-token>"
}
```

#### `POST /api/otp/validate/{token}`

Body:

```json
{
  "otp": "123456"
}
```

#### `POST /api/otp/resend/{token}`

Body: empty JSON object is fine.

Notes:

- Returns `429` if resend is too soon.

## Public Booking and Patient-Facing Endpoints

These require API key headers. Some also require a patient Sanctum token.

### Booking discovery

#### `GET /api/doctor-availabilities`

Query:

- `year` optional integer
- `month` optional integer
- `start_date` optional date
- `end_date` optional date
- `doctor_ids[]` optional array of doctor IDs

Notes:

- Returns only `active` availabilities.
- Supported availability status values: `active`, `canceled`

#### `GET /api/doctor-availabilities/get-today-doctors`

Returns today’s available doctors for public booking.

#### `GET /api/doctor-availabilities/search-doctor`

Query:

- `query`
- date range via `start_date` + `end_date`, or `year` + `month`

#### `GET /api/doctor-availabilities/doctor/{doctorId}/get-dates`

Returns upcoming dates for the given doctor.

#### `GET /api/doctor-availabilities/search-booking-doctors`

Query:

- `query` optional
- `date` optional, defaults to today
- `type` required in practice, matches doctor `doctor_type`

### Booking creation

#### `POST /api/bookings/make-appointment`

Body:

```json
{
  "name": "Patient Name",
  "phone": "+94770000000",
  "email": "patient@example.com",
  "age": 30,
  "doctor_id": 12,
  "doctor_type": "specialist",
  "date": "2026-03-25",
  "user_id": "<patient-user-uuid>"
}
```

Rules and behavior:

- `doctor_type` allowed values here: `specialist`, `dental`
- `phone` is required when `user_id` is not supplied
- phone must already be OTP verified
- decreases doctor seat availability
- auto-creates user/patient when needed
- rejects duplicate booking for same patient + doctor + date

Response:

```json
{
  "doctor_name": "Dr. Example",
  "doctor_specialty": "Cardiology",
  "booking_number": 10,
  "date": "2026-03-25",
  "reference": "<bill-uuid>",
  "generated_at": "2026-03-24T10:00:00.000000Z",
  "bill_id": 123
}
```

### Contacts

#### `POST /api/contacts`

Body:

```json
{
  "name": "Sender",
  "email": "sender@example.com",
  "phone": "+94770000000",
  "message": "Hello"
}
```

Notes:

- At least one of `email` or `phone` is required.

Response:

```json
{
  "message": "Message received. We’ll be in touch soon. Thanks for reaching out!",
  "reference": "<reference>"
}
```

### Patient self-service with Sanctum

#### `GET /api/patient/user`

Returns the logged-in patient user.

#### `GET /api/patient/user-patients`

Returns patient profiles linked to the logged-in patient user.

#### `GET /api/bookings/patients/history`

Returns appointment history for the authenticated patient user.

#### `POST /api/patient/create-patient`

Creates a patient profile.

Body:

```json
{
  "name": "Patient Name",
  "age": 30,
  "address": "Address",
  "gender": "male",
  "birthday": "1996-02-14",
  "telephone": "+94770000000",
  "email": "patient@example.com"
}
```

#### `PUT /api/patient/update-patient/{patient}`

Same fields as create, all optional except controller-side validation for fields supplied.

#### `PUT /api/patient/update-profile`

Body:

```json
{
  "name": "Updated Name",
  "email": "patient@example.com",
  "phone": "+94770000000"
}
```

#### `POST /api/patient/change-password`

Body:

```json
{
  "current_password": "old-secret",
  "new_password": "new-secret",
  "new_password_confirmation": "new-secret"
}
```

#### `POST /api/patient/logout`

No body required.

#### `DELETE /api/patient/delete-patient/{patient}`

Deletes the patient record.

## Staff Session Endpoints

These use Sanctum, but do not use the API-key middleware in the route list.

#### `GET /api/check-user`

Returns the authenticated user.

#### `GET /api/check-user-session`

Returns a session summary:

```json
{
  "message": "Logged in successfully",
  "token": "<current-access-token>",
  "role": "admin",
  "name": "User Name"
}
```

#### `POST /api/logout`

Revokes tokens for the logged-in user.

## Bills and Workflow Endpoints

### Core bills

#### `GET /api/bills`

- Role: `admin`, `reception`

#### `POST /api/bills`

- Role: `admin`, `reception`

Body:

```json
{
  "bill_amount": 2500,
  "payment_type": "cash",
  "system_amount": 300,
  "patient_id": 1,
  "doctor_id": 12,
  "is_booking": true,
  "service_type": "specialist",
  "shift": "morning",
  "date": "2026-03-25",
  "bill_id": 123,
  "bill_reference": "EXT-001"
}
```

Allowed `payment_type` values:

- `cash`
- `card`
- `online`

Important behavior:

- `status` becomes `booked` when `is_booking=true`, otherwise `doctor`
- `date` is required only for bookings
- controller also reads optional `bill_id` and `bill_reference`
- inserts bill items automatically
- may return a duplicate-booking warning

Possible status values seen in code:

- `booked`
- `doctor`
- `done`
- `pharmacy`
- `reception`
- `treatment`

#### `GET /api/bills/{bill}`

- Role: `admin`, `reception`

#### `PUT|PATCH /api/bills/{bill}`

- Role: `admin`, `reception`
- Body: `{ "status": "done" }`

#### `DELETE /api/bills/{bill}`

- Role: `admin`, `reception`
- Implementation expects the bill UUID string, not the numeric DB ID.
- Only non-`done` bills can be deleted.

### Bill queue / status workflow

#### `GET /api/bills/bookings/{time?}`

- Sanctum + role `reception|admin`
- `time` filter values are driven by enum code and intended usage is `today`, `future`, `old`

#### `GET /api/bills/pending/doctor`

- Sanctum + role `doctor`
- Query in practice: `doctor_id=<doctor-id>`

#### `GET /api/bills/pending/pharmacy`

- Sanctum + role `pharmacy|pharmacy_admin|doctor`

#### `GET /api/bills/pending/reception`

- Sanctum + role `reception|admin`

#### `PUT /api/bills/{billId}/send-to-reception`

Body:

```json
{
  "status": "reception",
  "bill_amount": 2500,
  "system_amount": 300
}
```

#### `PUT /api/bills/{billId}/status`

Body:

```json
{
  "status": "pharmacy"
}
```

Notes:

- when changed to `done`, payment status becomes paid
- when changed to `pharmacy`, medicine bill item may be auto-created

#### `PUT /api/bills/{billId}/change-temp-status`

- Sanctum + role `reception|nurse`

Body:

```json
{
  "patient_id": 1,
  "doctor_id": 12,
  "bill_amount": 2500,
  "is_booking": false
}
```

### Bill sub-resources

#### `GET /api/bills/{billId}/bill-items`

- Sanctum + role `reception|pharmacy|pharmacy_admin|admin|doctor`

#### `GET /api/bills/{billId}/sales`

- Sanctum + role `reception|pharmacy|pharmacy_admin|admin|doctor`

## Medical History Endpoints

### Patient history notes

#### `POST /api/patients/add-history`

- Sanctum + role `doctor` + ensure doctor

Body:

```json
{
  "patient_id": 1,
  "note": "Clinical note"
}
```

#### `GET /api/doctors/patient/{patientId}/histories`

- Sanctum + role `doctor` + ensure doctor

### Patient allergies

#### `POST /api/patients/add-allergy`

- Sanctum + role `doctor`

Body:

```json
{
  "patient_id": 1,
  "allergy_name": "Penicillin"
}
```

#### `DELETE /api/patients/remove-allergy/{allergyId}`

- Sanctum + role `doctor`

Body:

```json
{
  "patient_id": 1
}
```

### Patient diseases

#### `POST /api/patients/add-disease`

- Sanctum + role `doctor`

Body:

```json
{
  "patient_id": 1,
  "disease_name": "Diabetes"
}
```

#### `DELETE /api/patients/remove-disease/{diseaseId}`

- Sanctum + role `doctor`

Body:

```json
{
  "patient_id": 1
}
```

### Patient medicine history

#### `POST /api/patients/add-medicine`

- Sanctum + role `doctor` + ensure doctor

Body:

```json
{
  "patient_id": 1,
  "bill_id": 123,
  "brand_id": 4,
  "medication_frequency_id": 2,
  "medication_frequency_name": "Twice Daily",
  "duration": "5 days",
  "quantity": 10,
  "medicine_name": "Medicine Name"
}
```

Notes:

- if `medication_frequency_id = "-1"`, controller creates a new medication frequency using `medication_frequency_name`
- may return `409` on insufficient stock

#### `GET /api/doctors/patient/{patientId}/medicine-histories`

- Sanctum + role `pharmacy|doctor|admin`

#### `GET /api/doctors/patient/bill/{billId}/medicine-histories`

- Sanctum + role `doctor|pharmacy`

#### `DELETE /api/patient-medicine-histories/{patient_medicine_history}`

- Role in route: `admin|reception|pharmacy|pharmacy_admin|doctor`

## Search, Dropdown, and Reporting

### Patient search

#### `GET /api/patients/search`

- Sanctum + role `reception`
- Query: `query=<name-or-phone>`

### Generic dropdown

#### `GET /api/dropdown/{table}`

- Sanctum required
- Query: depends on table strategy
- Optional query: `limit`, default `10`

Useful for AI clients when IDs are needed before create/update calls.

### Reports

All `/api/reports/*` routes require Sanctum + role `admin`.

#### `GET /api/reports`

Query:

- `startDate` optional
- `endDate` optional

Returns:

- `billStatusSummary`
- `dailyReportSummary`
- `revenueByDoctor`
- `totalRevenue`

#### `GET /api/reports/service-costs`

Query:

- `start_date`
- `end_date`

#### `GET /api/reports/services-with-positive-system-amount`

Query:

- `start_date`
- `end_date`

## Resource CRUD Summary

Below are the create/update fields defined in request classes or controller validation.

### `allergies`

- Role: `admin`
- Create: `name`
- Update: `name`

### `bill-cruds`

- Role: `admin|reception`
- Request classes exist but currently define no explicit validation rules

### `bill-items`

- Role: `admin|pharmacy_admin|pharmacy|reception|doctor`
- Create: `patient_id?`, `bill_id`, `service_id`, `bill_amount`, `system_amount`, `service_name?`
- Update: `bill_amount?`, `system_amount?`
- Special behavior:
  - `service_id = "-1"` creates a new service using `service_name`
  - `bill_id = -1` creates a temporary treatment bill

### `brands`

- Role: `admin|pharmacy_admin`
- Create: `name`, `drug_id`
- Update: `name`, `drug_id`

### `categories`

- Role: `admin|pharmacy_admin`
- Create: `name`
- Update: `name`

### `diseases`

- Role: `admin`
- Create: `name`
- Update: `name`

### `doctor-channeling-fees`

- Index/show/store/update/destroy role: `admin`
- `GET /api/doctor-channeling-fees/get-fee/{id}` role: `reception`
- Create: `doctor_id`, `fee`
- Update: `id`, `fee`

### `doctors`

- Role: `admin|reception`
- Create: `name`, `hospital_id?`, `user_id?`, `specialty_id?`, `telephone`, `email?`, `doctor_type?`
- Update: same fields, optional
- `doctor_type` values: `dental`, `opd`, `specialist`, `treatment`
- Extra route: `GET /api/doctors/{doctor}/availabilities` for admin

### `doctors-availabilities`

- Role: `admin|reception`
- Create: `doctor_id`, `status`, `date`, `seats`, `available_seats`, `time`
- Update: same fields, optional
- `status` values: `active`, `canceled`

### `doctors-schedules`

- Role: `admin|reception`
- Create: `doctor_id`, `weekday`, `time`, `recurring`, `seats`, `status`
- Update: same fields, optional
- `status` values: `active`, `inactive`
- Create/update also regenerate doctor availabilities for the month

### `drugs`

- Role: `admin|pharmacy_admin`
- Create: `name`, `minimum_quantity`, `category_id`
- Update: `name`, `minimum_quantity?`, `category_id?`
- Extra route: `GET /api/drugs/stock-sale-data`
  - query: `q`, `per_page`

### `hospitals`

- Role: `admin`
- Create: `name`, `location`
- Update: `name?`, `location?`

### `patients`

- Role: `admin|reception`
- Index query: optional `search` on telephone
- Create: `name`, `age`, `address?`, `gender?`, `birthday?`, `telephone`, `email?`
- Update: same fields, optional

### `patient-medicine-histories`

- Role: `admin|reception|pharmacy|pharmacy_admin|doctor`
- Route file exposes resource endpoints, but the controller explicitly implements only `store`, `destroy`, and custom history reads

### `roles`

- Role: `admin`
- Create: `name`, `key`, `description?`
- Update: `id`, `name`, `key`, `description?`

### `sales`

- Role: `admin|pharmacy_admin|reception|pharmacy|doctor`
- Create: `brand_id`, `bill_id`, `quantity`, `total_price`
- Update: `brand_id`, `bill_id`, `quantity`, `total_price`
- Extra route: `PATCH /api/sales/update-quantity`
  - body: `sale_id`, `quantity`
- Extra route: `PUT /api/sales/update-number-of-days`
  - body: `patient_medicine_history_id`, `number_of_days`

### `services`

- Role: `admin`
- Create: `name`, `key`, `bill_price`, `system_price`
- Update: `name?`, `bill_price?`, `system_price?`

### `specialties`

- Role: `admin`
- Create: `name`
- Update: `name`

### `stocks`

- Role: `admin|pharmacy_admin`
- Create: `brand_id`, `supplier_id`, `unit_price`, `batch_number?`, `initial_quantity`, `quantity`, `expire_date`, `cost?`
- Update: same fields, mostly required

### `suppliers`

- Role: `admin|pharmacy_admin`
- Create: `name`, `email?`, `address?`, `phone`
- Update: `name`, `email?`, `address?`, `phone`

### `trusted-sites`

- Role: `admin`
- Request classes currently define no explicit validation rules
- This resource controls which `Referer` hosts are allowed for `X-API-KEY`

### `users`

- Role: `admin`
- Create: `email`, `name`, `role_id`, `password`
- Update: `id`, `email`, `name`, `role_id`, `password`
- Extra route: `POST /api/users/create-from-doctor`
  - Sanctum + role `admin`
  - body: `{ "doctor_id": 12 }`

## Route Inventory With Known Special Cases

### Registered but likely incomplete

These routes exist in `routes.json`, but the current codebase does not provide a matching controller method or full implementation for normal API usage:

- `GET /api/bookings/doctors/list`
- `POST /api/admin/services`
- `GET /api/admin/services/create`
- `GET /api/admin/services/{service}`
- `PUT|PATCH /api/admin/services/{service}`
- `DELETE /api/admin/services/{service}`
- `GET /api/admin/services/{service}/edit`

For `api/admin/services`, only `index()` is implemented in `ServiceAdminController`.

### Behavioral quirks worth knowing

- `DELETE /api/bills/{bill}` uses bill UUID, not numeric ID.
- Trait-based list endpoints paginate at 30 records by default.
- Some success/error responses are plain JSON strings instead of objects.
- `PUT /api/sales/update-number-of-days` validates `patient_medicine_history_id` against the `sales` table in the request class, but the controller loads `PatientMedicineHistory` by that same ID. Treat this endpoint carefully.

## Suggested AI Agent Workflow

1. Send `X-API-KEY` and a valid `Referer` on nearly all `/api/*` requests.
2. Use public discovery endpoints such as doctor availability and dropdowns to resolve IDs.
3. Log in only when the route also requires Sanctum.
4. Respect role restrictions before attempting private workflow routes.
5. Prefer the documented custom endpoints for bills, bookings, and patient histories instead of guessing resource behavior from route names.
