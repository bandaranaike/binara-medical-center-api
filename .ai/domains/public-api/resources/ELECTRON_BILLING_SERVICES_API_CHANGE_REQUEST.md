# Electron Billing Services API Change Request

This document defines the backend/API changes needed for the Electron medical-center billing desk as of `2026-04-01`.

It is intended for:

- the backend API team
- an API-focused agent working in the Laravel project
- future Electron app work that must align with the same billing contract

## Why This Change Is Needed

The Billing Desk now needs to support:

- dynamic service entry in `Others`
- autocomplete for service names
- separate `In-house` and `Referred` amounts for applicable service items
- doctor-driven price autofill for Channeling and Dental
- bills in `Others` that may not have a doctor

The current Electron app can now prepare richer bill items, but the backend public API still needs a stable contract to accept and return those items consistently.

## Current Gaps

From the current route inventory and API notes:

- there is no documented reception-safe or public service autocomplete endpoint
- the current public bill create flow is documented around top-level totals, not structured split-price bill items
- the existing `services` CRUD is admin-oriented and is not appropriate for receptionist-facing search in the Electron app
- the existing documented doctor channeling fee route does not cover all pricing needs for Channeling + Dental + dynamic Others

## Required Backend Outcomes

The backend should provide all of the following:

1. receptionist-safe service lookup for autocomplete
2. bill creation/update endpoints that accept split-price line items
3. bill/read/list endpoints that return split-price line items back to the app
4. doctor-based pricing lookup for Channeling and Dental
5. support for bills without a doctor in `Others`

## Auth Expectations

These Electron routes should follow the same app integration rules already used elsewhere:

- `Accept: application/json`
- `Content-Type: application/json`
- `X-API-KEY: <trusted-site-api-key>`
- `Referer: <trusted-site-domain>`
- `Authorization: Bearer <public-app-token>`

If some routes must remain staff-only, document that clearly and provide a receptionist-safe equivalent for the Electron app.

## 1. Service Autocomplete Endpoint

## Recommended endpoint

`GET /api/public/services/search`

## Query parameters

- `query`: required string, minimum 2 characters
- `type`: optional string

Allowed `type` values:

- `opd`
- `specialist`
- `dental`
- `treatment`

## Behavior

- search by service name
- optionally filter by service type or usage category
- return results suitable for receptionist autocomplete
- include both `system_price` and `bill_price`
- include enough metadata for the Electron app to know whether the service is an existing backend service or an ad hoc typed one

## Success response

```json
{
  "data": [
    {
      "id": 12,
      "name": "Wound Dressing",
      "key": "wound-dressing",
      "type": "treatment",
      "system_price": 500.00,
      "bill_price": 800.00
    }
  ]
}
```

## Notes

- `system_price` maps to `In-house`
- `bill_price` maps to the full referred-facing amount already used in the service table today
- for the Electron app, `referred_amount` should be interpreted as the operator-editable referred portion, not as a derived value hidden in backend logic

## 2. Doctor Pricing Lookup Endpoint

## Recommended endpoint

`GET /api/public/doctors/{doctor}/billing-config`

## Purpose

Return billing defaults for the selected doctor so the Electron app can autofill Channeling and Dental charges.

## Success response

```json
{
  "doctor_id": 7,
  "doctor_type": "specialist",
  "channeling": {
    "consultation_referred_amount": 2500.00,
    "booking_in_house_amount": 300.00
  },
  "dental": {
    "registration_in_house_amount": 500.00,
    "services": [
      {
        "service_id": 41,
        "name": "Dental Consultation",
        "system_price": 800.00,
        "bill_price": 1500.00
      },
      {
        "service_id": 42,
        "name": "Dental Medicine",
        "system_price": 0.00,
        "bill_price": 1200.00
      }
    ]
  }
}
```

## Notes

- if existing `doctor-channeling-fees` data can be reused, that is good
- if Dental needs doctor-specific overrides beyond the current schema, add a doctor-service pricing table or equivalent mapping
- this endpoint should be safe for receptionist/Electron usage

## 3. Public Bill Create/Update Payload

The Electron app now needs a line-item-aware payload.

## Affected endpoints

- `POST /api/public/bills`
- `PUT /api/public/bookings/{id}`
- `POST /api/public/bookings/{id}/proceed-to-payment`

## Required request body fields

Existing top-level fields can remain, but the backend should also accept structured `items`.

```json
{
  "bill_amount": 3300.00,
  "system_amount": 800.00,
  "payment_type": "cash",
  "patient_id": 15,
  "doctor_id": null,
  "is_booking": false,
  "service_type": "treatment",
  "shift": "morning",
  "date": "2026-04-01",
  "items": [
    {
      "service_id": 12,
      "service_key": "wound-dressing",
      "service_name": "Wound Dressing",
      "bill_amount": 1300.00,
      "system_amount": 500.00,
      "referred_amount": 800.00,
      "category": "others",
      "doctor_id": null,
      "is_ad_hoc": false
    },
    {
      "service_id": -1,
      "service_key": null,
      "service_name": "Special Report",
      "bill_amount": 2000.00,
      "system_amount": 300.00,
      "referred_amount": 1700.00,
      "category": "others",
      "doctor_id": null,
      "is_ad_hoc": true
    }
  ]
}
```

## Required rules

- `doctor_id` must be allowed to be `null` for `service_type = treatment` / `Others`
- `bill_amount` at the top level must equal the sum of item `bill_amount`
- `system_amount` at the top level must equal the sum of item `system_amount`
- `referred_amount` should be stored or reproducible for each bill item
- if `service_id = -1` or `is_ad_hoc = true`, the backend should support ad hoc service names without breaking bill creation

## 4. Bill Item Persistence Rules

The backend should treat each Electron billing row as a concrete bill item.

## Required persisted fields per item

- `bill_id`
- `service_id` or an explicit ad hoc representation
- `service_name` snapshot for historical printing/audit
- `bill_amount`
- `system_amount`
- `referred_amount`
- optional `doctor_id`
- optional category/type marker such as `opd`, `channeling`, `dental`, `others`

## Schema note

Current references show:

- `bill_items.bill_amount`
- `bill_items.system_amount`
- no dedicated `referred_amount` column in the current schema dump

Recommended options:

1. add `bill_items.referred_amount`
2. or guarantee `referred_amount = bill_amount - system_amount` and return it explicitly in API responses

Option `1` is preferred because it keeps the contract explicit and avoids ambiguity when future billing logic changes.

## 5. Bill/Booking Read Response Shape

When the Electron app loads bookings or existing bills, item responses should include split amounts.

## Required item response shape

```json
{
  "items": [
    {
      "service_id": 12,
      "service_key": "wound-dressing",
      "service_name": "Wound Dressing",
      "bill_amount": 1300.00,
      "system_amount": 500.00,
      "referred_amount": 800.00
    }
  ]
}
```

## Important

Do not return only one merged `price` value if the backend expects the Electron app to re-edit split pricing later.

## 6. Operation-Specific Rules

## Channeling

- doctor consultation fee is `Referred`
- booking/channeling fee is `In-house`
- doctor-based lookup should autofill both values when available

## Dental

- registration fee is `In-house`
- other dental services must allow separate `In-house` and `Referred` values
- doctor-based defaults for common dental services should be available when possible

## Others

- doctor is optional
- operator can type ad hoc services
- service autocomplete should still work when matching a known backend service
- each row must support separate `In-house` and `Referred` values

## 7. Backend Validation Guidance

Recommended validation rules for item payloads:

- `items`: required array with at least one item when creating/updating a bill from Electron
- `items.*.service_name`: required string
- `items.*.bill_amount`: required numeric min `0`
- `items.*.system_amount`: required numeric min `0`
- `items.*.referred_amount`: required numeric min `0`
- `items.*.doctor_id`: nullable existing doctor id
- `items.*.service_id`: nullable or `-1` for ad hoc
- `items.*.category`: nullable enum `opd|channeling|dental|others`

Recommended consistency rule:

- `items.*.bill_amount == items.*.system_amount + items.*.referred_amount`

## 8. Suggested Implementation Order

1. add service autocomplete endpoint
2. add doctor billing config endpoint
3. extend public bill/create-update endpoints to accept structured items
4. extend booking/bill read responses to return split item data
5. update any printer/report logic that depends on bill items

## 9. Electron App Status

The Electron repo now prepares richer billing item payloads and sends:

- top-level `system_amount`
- per-item `system_amount`
- per-item `referred_amount`
- ad hoc item metadata

Backend changes are still required before the full workflow becomes API-complete.
