# API AI Handoff: Multiple Patients Per Telephone Number

## Objective

Change the backend so a telephone number is a searchable contact value, not a
unique patient identity. Different patients may have the same telephone
number, while each patient remains uniquely addressable by `patients.id`.

## Required backend changes

1. Remove any `unique` database index/constraint on `patients.telephone` (the
   current schema reference already shows no telephone unique constraint).
2. Remove `telephone|unique` validation from patient create/update rules. Keep
   telephone required for reception-created patients unless the product rules
   change.
3. Update `GET /api/public/patients/search?query=...`:
   - search name, telephone, email, and registration number as currently
     supported;
   - return every matching patient the caller is allowed to see, not a
     `first()` result;
   - include `id`, `name`, `telephone`, `email`, `birthday`/`dateOfBirth`,
     `age`, `gender`, `address`, and `registration_no`/`registrationNo`;
   - use a deterministic order and a reasonable limit (for example 20), with
     pagination if the existing endpoint supports it.
4. Ensure `POST /api/public/patients` always creates a new patient when no
   `patient_id` is supplied. It must not find-or-update by telephone.
5. Ensure `PUT /api/public/patients/{patient}` updates only the ID in the URL.
   Telephone matching must never redirect the update to another patient.
6. Extend `POST /api/bookings/make-appointment` to accept optional
   `patient_id`. When present, validate that patient and use it for the bill /
   appointment; do not resolve another patient by phone. When absent, preserve
   the existing auto-create behavior for other clients.
7. Extend `PUT /api/public/bookings/{booking}` to accept `patient_id` and use
   that patient for the updated booking. If the nested `patient` object is
   retained for profile updates, apply those updates to this exact ID only.

## Request examples

### Create a new patient with an existing telephone

```json
{
  "name": "Another Family Member",
  "telephone": "+94770000000",
  "age": 12,
  "gender": "female"
}
```

This must create a second `patients` row; it must not update the existing row.

### Create a booking for a selected patient

```json
{
  "patient_id": 456,
  "name": "Selected Patient",
  "phone": "+94770000000",
  "age": 12,
  "doctor_id": 12,
  "doctor_type": "specialist",
  "date": "2026-08-27"
}
```

`patient_id` is authoritative. The name/phone fields may be retained for
backward compatibility and consistency validation, but must not select a
different patient.

## Compatibility and safety

- Keep `X-API-KEY` and `Referer` requirements unchanged for public routes.
- Preserve existing Sanctum/role middleware behavior.
- Keep registration number uniqueness unchanged.
- Do not change phone verification uniqueness for patient login accounts; this
  request concerns reception `patients` records and their telephone field.
- Add feature tests for two patients sharing a telephone, search returning both,
  billing each patient separately, and booking the second patient by ID.

## Desktop dependency

The desktop app now creates a new patient unless the operator selected a search
result, and sends `patient_id` for booking create/update. Deploy this backend
contract before enabling the updated booking flow in production.
