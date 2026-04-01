# Current Task

## Title

Add bill day-summary API for printed service totals

## Status

Completed

## Goal

Create an API endpoint that returns a printable day summary grouped by service for a selected date and shift.

## Work items

- add a request validator for date and shift
- add an admin reporting endpoint under `/api/reports/*`
- aggregate paid bill items into printer-friendly rows
- include doctor name in channeling rows
- cover the endpoint with feature tests

## Notes

- use `bills.date` as the report date filter
- use `bill_items.bill_amount` for totals and `COUNT(bill_items.id)` for quantity
- filter to paid, non-deleted bills and exclude zero-total rows
