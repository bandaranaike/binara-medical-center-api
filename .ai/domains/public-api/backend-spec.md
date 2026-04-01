# Public API Backend Specification

This is a `domains/public-api/` reference note for the Electron-facing public API surface.

## Purpose

This document defines the new public API endpoints required by the Electron medical-center application.

The target backend AI agent should use this file to implement a new public API surface that:

- works with token-based authentication
- does not require browser session or CSRF flow
- does not depend on interactive staff login
- supports patient search, patient upsert, doctor list, and bill creation for the desktop billing workflow

## Authentication Requirements

All endpoints in this document must be protected by app-level token authentication.

### Required headers for every request

```http
Accept: application/json
Content-Type: application/json
X-API-KEY: <trusted-site-api-key>
Referer: <trusted-application-origin>
Authorization: Bearer <public-app-token>
```

### Authentication behavior

- `X-API-KEY` must be validated against the trusted site configuration
- `Referer` must match an allowed trusted app domain or origin
- `Authorization: Bearer <token>` must validate an application token usable by the Electron app
- no session cookie is required
- no Sanctum CSRF cookie flow is required
- no user login is required before using these endpoints

## Route Group Recommendation

Recommended route prefix:

- `/api/public`

Recommended middleware behavior:

- keep existing old routes unchanged
- implement a separate route group for desktop public API access
- use app-token middleware instead of role-based user authentication

## Common Response Rules

- all responses must be JSON
- validation errors should return `422`
- unauthorized or invalid token should return `401`
- forbidden trusted-site or API-key mismatch should return `403`
- missing record should return `404`
- successful create should return `201` where practical

Suggested validation error shape:

```json
{
  "message": "Validation failed",
  "errors": {
    "field_name": [
      "The field_name field is required."
    ]
  }
}
```

## Domain Requirements From Desktop App

The Electron app needs these business flows:

- search patient by telephone
- search patient by name
- autofill patient details
- create patient if not found
- update patient if found
- load doctors for billing selection
- create a bill for OPD, Channeling, Dental, or Others workflows

## Endpoint 1: Search Patients

### Summary

Search patients by telephone or patient name for autofill in the billing form.

### Method and path

`GET /api/public/patients/search`

### Query parameters

- `query` required string

### Request example

`GET /api/public/patients/search?query=0771234567`

### Validation rules

- `query` is required
- `query` must be a non-empty string
- partial matching is allowed
- search should match:
  - patient telephone
  - patient name

### Success response

Status: `200`

```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "telephone": "+94771234567",
      "email": "john@example.com",
      "age": 30,
      "gender": "male",
      "address": "Colombo"
    }
  ]
}
```

### Required response fields

- `id`
- `name`
- `telephone`
- `email`
- `age`
- `gender`
- `address`

### Notes

- results should be ordered by best match first
- exact telephone matches should appear before partial name matches
- response can be empty:

```json
{
  "data": []
}
```

## Endpoint 2: Create Patient

### Summary

Create a new patient record for billing.

### Method and path

`POST /api/public/patients`

### Request body

```json
{
  "name": "John Doe",
  "telephone": "+94771234567",
  "email": "john@example.com",
  "age": 30,
  "gender": "male",
  "address": "Colombo"
}
```

### Required parameters

- `name` string
- `telephone` string
- `age` integer or numeric value

### Optional parameters

- `email` string nullable
- `gender` string nullable
- `address` string nullable
- `birthday` date nullable

### Validation rules

- `name` required
- `telephone` required
- `age` required
- `email` optional but must be valid email if provided
- `gender` optional but should allow:
  - `male`
  - `female`
  - `other`
- `address` optional
- `birthday` optional date

### Success response

Status: `201`

```json
{
  "id": 1,
  "name": "John Doe",
  "telephone": "+94771234567",
  "email": "john@example.com",
  "age": 30,
  "gender": "male",
  "address": "Colombo",
  "birthday": null
}
```

### Required response fields

- `id`
- `name`
- `telephone`
- `email`
- `age`
- `gender`
- `address`

### Notes

- if duplicate handling is implemented, use clear behavior
- preferred behavior:
  - if exact patient already exists by telephone, return `409`
  - message should clearly explain duplicate patient

Suggested duplicate response:

```json
{
  "message": "Patient already exists for the given telephone number."
}
```

## Endpoint 3: Update Patient

### Summary

Update an existing patient record used in billing.

### Method and path

`PUT /api/public/patients/{id}`

### Path parameters

- `id` required integer patient ID

### Request body

```json
{
  "name": "John Doe",
  "telephone": "+94771234567",
  "email": "john@example.com",
  "age": 31,
  "gender": "male",
  "address": "Kandy"
}
```

### Allowed body parameters

- `name`
- `telephone`
- `email`
- `age`
- `gender`
- `address`
- `birthday`

### Validation rules

- path `id` must exist in patients table
- all fields optional in partial-update implementation, but full `PUT` response must still return the full patient object
- if provided:
  - `email` must be valid
  - `age` must be numeric
  - `gender` should be one of `male`, `female`, `other`

### Success response

Status: `200`

```json
{
  "id": 1,
  "name": "John Doe",
  "telephone": "+94771234567",
  "email": "john@example.com",
  "age": 31,
  "gender": "male",
  "address": "Kandy",
  "birthday": null
}
```

## Endpoint 4: List Doctors

### Summary

Return doctor list for the billing screen.

### Method and path

`GET /api/public/doctors`

### Query parameters

- `sort[]` optional
- `doctor_type` optional
- `search` optional

### Supported query behavior

- `sort[]=name`
- `sort[]=name:desc`
- `doctor_type=opd`
- `doctor_type=specialist`
- `doctor_type=dental`
- `doctor_type=treatment`
- `search=<doctor-name>`

### Success response

Status: `200`

```json
{
  "data": [
    {
      "id": 12,
      "name": "Dr. Example",
      "telephone": "+94770000001",
      "email": "doctor@example.com",
      "doctor_type": "specialist",
      "specialty_name": "Cardiology"
    }
  ]
}
```

### Required response fields

- `id`
- `name`
- `telephone`
- `email`
- `doctor_type`
- `specialty_name`

### Notes

- only active or usable doctors for billing should be returned if such status exists in backend rules
- sort by `name` should be supported because the current Electron app uses it

## Endpoint 5: Create Bill

### Summary

Create a bill for the selected patient and doctor in the desktop billing workflow.

### Method and path

`POST /api/public/bills`

### Request body

```json
{
  "bill_amount": 2500,
  "payment_type": "cash",
  "system_amount": 0,
  "patient_id": 1,
  "doctor_id": 12,
  "is_booking": false,
  "service_type": "opd",
  "shift": "morning",
  "date": "2026-03-25"
}
```

### Required parameters

- `bill_amount` numeric
- `payment_type` string
- `system_amount` numeric
- `patient_id` integer
- `doctor_id` integer
- `is_booking` boolean
- `service_type` string
- `shift` string
- `date` date string

### Allowed values

#### `payment_type`

- `cash`
- `card`
- `online`

#### `service_type`

- `opd`
- `specialist`
- `dental`
- `treatment`

#### `shift`

- `morning`
- `evening`

### Validation rules

- `patient_id` must exist
- `doctor_id` must exist
- `bill_amount` must be numeric and zero or greater
- `system_amount` must be numeric and zero or greater
- `payment_type` must be one of the allowed values
- `service_type` must be one of the allowed values
- `shift` must be one of the allowed values
- `date` must be a valid date

### Success response

Status: `201`

```json
{
  "id": 123,
  "uuid": "generated-bill-uuid",
  "patient_id": 1,
  "doctor_id": 12,
  "bill_amount": 2500,
  "system_amount": 0,
  "payment_type": "cash",
  "payment_status": "pending",
  "status": "doctor",
  "service_type": "opd",
  "shift": "morning",
  "date": "2026-03-25"
}
```

### Required response fields

- `id`
- `uuid` or `reference`
- `patient_id`
- `doctor_id`
- `bill_amount`
- `system_amount`
- `payment_type`
- `payment_status`
- `status`
- `service_type`
- `shift`
- `date`

### Notes

- for the desktop app, `is_booking` will usually be `false`
- if backend stores service type in another field such as `appointment_type`, map it clearly in the response
- if duplicate-booking logic exists, only apply it when relevant

## Optional Endpoint 6: Create Or Update Patient by Telephone

### Summary

This endpoint is optional but recommended if the backend wants to simplify desktop integration.

### Method and path

`POST /api/public/patients/upsert`

### Request body

```json
{
  "name": "John Doe",
  "telephone": "+94771234567",
  "email": "john@example.com",
  "age": 30,
  "gender": "male",
  "address": "Colombo"
}
```

### Behavior

- find patient by exact telephone
- if found, update patient
- if not found, create patient

### Success response

Status: `200`

```json
{
  "action": "updated",
  "patient": {
    "id": 1,
    "name": "John Doe",
    "telephone": "+94771234567",
    "email": "john@example.com",
    "age": 30,
    "gender": "male",
    "address": "Colombo"
  }
}
```

or

```json
{
  "action": "created",
  "patient": {
    "id": 2,
    "name": "John Doe",
    "telephone": "+94771234567",
    "email": "john@example.com",
    "age": 30,
    "gender": "male",
    "address": "Colombo"
  }
}
```

## Implementation Notes for Backend Agent

### Existing backend context

The current backend already has:

- `patients` table
- `doctors` table
- `bills` table
- trusted site API key behavior
- old role-protected routes

### Important design instruction

Do not replace or break the old authenticated staff routes.

Instead:

- add a new public token-authenticated API group
- keep old web and staff workflow routes unchanged
- make this new group suitable for the Electron desktop application

### Suggested middleware design

Recommended middleware chain for the new route group:

- API JSON middleware
- trusted site / API key verification
- referer validation
- app token validation middleware

Avoid:

- session auth
- CSRF dependency
- role-based middleware tied to interactive staff user login

## Minimal Field Mapping Notes

### Patient

Backend source table:

- `patients`

Expected fields:

- `id`
- `name`
- `telephone`
- `email`
- `age`
- `gender`
- `address`
- `birthday` optional

### Doctor

Backend source tables:

- `doctors`
- `specialties`

Expected fields:

- `id`
- `name`
- `telephone`
- `email`
- `doctor_type`
- `specialty_name`

### Bill

Backend source table:

- `bills`

Expected fields:

- `id`
- `uuid`
- `patient_id`
- `doctor_id`
- `bill_amount`
- `system_amount`
- `payment_type`
- `payment_status`
- `status`
- `shift`
- `date`

## Priority Order

If implementation must be phased, use this order:

1. `GET /api/public/patients/search`
2. `POST /api/public/patients`
3. `PUT /api/public/patients/{id}`
4. `GET /api/public/doctors`
5. `POST /api/public/bills`

## Final Requirement Summary

The new public API must allow the Electron app to:

- authenticate with configured token headers
- search patients by name or telephone
- create patients
- update patients
- list doctors
- create bills

without needing session login, browser cookies, or old role-bound web authentication flows.
