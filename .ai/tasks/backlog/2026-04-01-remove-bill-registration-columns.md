# Task

## Title

Remove `bill_registration_number` and `booking_registration_number`

## Status

Completed

## Goal

Remove `bills.bill_registration_number` and `bills.booking_registration_number` from the database schema and stop using them across validation, controllers, resources, and API responses.

## Why

- The columns are no longer required.
- The current implementation still treats them as part of the billing and booking flow.
- Keeping unused identifiers in the API increases payload noise and future maintenance cost.

## Scope

- add a migration to drop both columns from `bills`
- update Eloquent model fillable / casts / access patterns if present
- remove controller and trait logic that generates, stores, or updates these values
- remove request validation rules that require or accept these values
- remove resource / serializer output that exposes these values
- update any booking or billing endpoints that still depend on them
- update tests that assert these fields exist

## Likely Code Areas

- `app/Models/Bill.php`
- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/BillController.php`
- `app/Http/Controllers/PublicApi/*`
- `app/Http/Controllers/Traits/*`
- `app/Http/Requests/*`
- `app/Http/Resources/*`
- `database/migrations/*`
- `tests/Feature/*`
- `tests/Unit/*`

## Acceptance Criteria

- `bills` no longer has `bill_registration_number`
- `bills` no longer has `booking_registration_number`
- booking creation and bill creation still work without either field
- no public or private API response includes either field unless there is a documented compatibility reason
- no request validator expects either field
- affected automated tests pass

## Notes

- Search for both field names first because usage may exist in traits, resources, report queries, and public API controllers.
- If external clients still consume these fields, confirm whether a breaking API change is acceptable before release.
- Implemented on 2026-04-01 with a schema drop migration, API/resource cleanup, `uuid`-based references, and updated tests.
