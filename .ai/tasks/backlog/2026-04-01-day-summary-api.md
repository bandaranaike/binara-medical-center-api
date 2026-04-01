# Task

## Title

Add bill day-summary API for printed service totals

## Status

Completed

## Goal

Create an API endpoint that returns a printable service summary for a selected date and shift using `bills` and related line items.

## Why

- The clinic needs a daily printed sales summary.
- Morning and evening totals must be printable separately.
- The output is consumed by a Windows-side Python printer service.

## Consumer Contract

The downstream printer service expects a payload compatible with this shape:

```json
{
  "start_date": "2026-04-01",
  "end_date": "2026-04-01",
  "items": [
    {
      "service_name": "Channeling Dr.Aruna",
      "quantity": 6,
      "total": 12000
    }
  ]
}
```

Observed consumer expectations from `print_summary.py`:

- `start_date` is a string
- `end_date` is a string
- `items` is an array
- each item contains `service_name`, `quantity`, and `total`
- `total` must be numeric or numeric-string parsable by Python `float()`

## Required Behavior

- add an API endpoint that accepts:
  - `date`, defaulting to today
  - `shift`, using `morning` or `evening`
- return only rows with non-zero totals
- group bill item output into print rows with:
  - `service_name`
  - `quantity`
  - `total`
- build `service_name` as:
  - `services.name + doctors.name` when `services.key == "channeling"`
  - `services.name` for all other services
- derive names through the existing foreign keys:
  - `bills.doctor_id -> doctors.id`
  - `bill_items.service_id -> services.id`

## Data Rules To Confirm In Implementation

- define whether the summary should filter by bill date column, created timestamp, or paid timestamp
- define which bill statuses count as sales for this summary
- define whether cancelled, refunded, soft-deleted, or unpaid bills must be excluded
- define whether quantity comes from `bill_items.qty`, `bill_items.quantity`, or another existing field name
- define whether total comes from the line item total or a calculated value

## Likely Code Areas

- `routes/api.php`
- `app/Http/Controllers/*`
- `app/Http/Requests/*`
- `app/Http/Resources/*`
- `app/Models/Bill.php`
- `app/Models/BillItem.php`
- `tests/Feature/*`

## Acceptance Criteria

- API supports a selected date and defaults to today when omitted
- API supports shift filtering for `morning` and `evening`
- output matches the Python consumer contract
- rows with zero total are excluded
- channeling rows include the doctor name in the service label
- endpoint is covered by automated tests for happy path and filtering rules

## References

- Printer consumer file provided by user: `C:\Users\eragr\binara-printer\print_summary.py`
- Visual example: `.ai/domains/public-api/resources/Summary-Bill.jpeg`

## Implementation Notes

- Implemented on 2026-04-01 at `GET /api/reports/day-summary`
- Uses `bills.date` for the selected day, defaults `date` to today, and requires `shift`
- Includes only paid, non-deleted bills
- Uses `COUNT(bill_items.id)` for quantity and `SUM(bill_items.bill_amount)` for total
- Builds channeling labels as `services.name + doctors.name`
