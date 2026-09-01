# Current Task

## Title

Unify bill amount fields across backend, public API, and frontend

## ID

TASK-129

## Status

In Progress

## Goal

Remove `bill_amount` from bills and bill items, use `referred_amount` and `system_amount` consistently, and expose calculated totals across the application and API contracts.

## Implementation

- added a migration that backfills referred amounts and removes both `bill_amount` columns
- synchronized bill-level split amounts from bill items after billing mutations
- updated internal resources, reports, printing, requests, and public API payloads
- updated the Electron frontend types, billing flows, tables, and reports
- updated schema and API documentation to define `total_amount` as calculated

## Verification

- focused backend coverage: 36 tests passed, 237 assertions
- frontend `npm run lint` and `npm run build` passed
