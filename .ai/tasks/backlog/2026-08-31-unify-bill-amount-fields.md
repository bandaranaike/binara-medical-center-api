# Task

## ID

TASK-129

## Title

Unify bill and bill-item amount fields across the application and APIs

## Status

In Progress

## Goal

Remove the ambiguous `bill_amount` columns from both `bills` and `bill_items`, introduce `referred_amount` on `bills`, and use one amount contract throughout the Laravel backend, `/home/eranda/test/test-f`, and all private/public APIs.

## Canonical Amount Contract

- `referred_amount`: the referred / doctor / customer portion of the charge.
- `system_amount`: the institution / system portion of the charge.
- `total_amount`: calculated only as `referred_amount + system_amount`; it is not persisted.
- At bill level, `bills.referred_amount` and `bills.system_amount` must equal the sums of their bill-item counterparts.
- `bill_items.referred_amount` and `bill_items.system_amount` are the authoritative line-item amounts.
- No request, response, report, print payload, frontend type, or calculation should use `bill_amount` after migration completion.

## Scope

- Add a migration that adds `bills.referred_amount`.
- Backfill `bill_items.referred_amount` from existing data using the currently established relationship between `bill_amount` and `system_amount`.
- Backfill `bills.referred_amount` from bill items and verify bill-level system totals against bill-item totals.
- Drop `bills.bill_amount` and `bill_items.bill_amount` only after the backfill and application migration are ready.
- Update Eloquent fillable fields, casts, relationships, services, traits, controllers, requests, resources, listeners, reports, and print data processing.
- Replace all amount validation with `referred_amount` and `system_amount` validation.
- Validate bill totals against the sum of item `referred_amount` and `system_amount`.
- Return a calculated `total_amount` wherever consumers need the complete charge.
- Remove `bill_amount` from private and public API request and response contracts.
- Update public booking, public bill, payment, listing, and serialization flows consistently.
- Update `/home/eranda/test/test-f` TypeScript interfaces, billing forms, amount inputs, totals, reports, booking lists, reception lists, pharmacy flows, and printing-related displays.
- Update API and frontend documentation that still describes `bill_amount`.

## Important Affected Areas

### Backend

- `app/Models/Bill.php`
- `app/Models/BillItem.php`
- `app/Services/PublicBillingService.php`
- `app/Services/DaySummaryReportService.php`
- `app/Http/Controllers/BillController.php`
- `app/Http/Controllers/BillItemController.php`
- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/PublicApi/PublicBillController.php`
- `app/Http/Controllers/PublicApi/PublicBookingController.php`
- `app/Http/Controllers/ReportController.php`
- `app/Http/Controllers/Traits/BillItemsTrait.php`
- `app/Http/Controllers/Traits/PrintingDataProcess.php`
- `app/Listeners/SyncMedicineBillItem.php`
- `app/Http/Requests/*`
- `app/Http/Resources/*`
- `database/migrations/*`

### Frontend

- `/home/eranda/test/test-f/types/interfaces.ts`
- `/home/eranda/test/test-f/components/Services.tsx`
- `/home/eranda/test/test-f/components/doctor/BillItemsManager.tsx`
- `/home/eranda/test/test-f/components/high-order-components/withBillingComponent.tsx`
- `/home/eranda/test/test-f/components/PharmacyPortal.tsx`
- `/home/eranda/test/test-f/components/BookingsTable.tsx`
- `/home/eranda/test/test-f/components/TodayList.tsx`
- `/home/eranda/test/test-f/components/popup/ShowBillAndPrint.tsx`
- `/home/eranda/test/test-f/components/reports/ServiceCostReport.tsx`

## API Contract Decision

This is an intentional breaking contract change. Public and private APIs must use:

```json
{
  "referred_amount": 1700.00,
  "system_amount": 300.00,
  "total_amount": 2000.00
}
```

The old `bill_amount` field must not remain as an alias after migration completion. Update all public API consumers at the same time or release the backend and frontend together.

## Data Safety Requirements

- Run read-only audits before changing the schema.
- Identify records where `bill_amount - system_amount` is negative.
- Identify records where existing `referred_amount` disagrees with the derived value.
- Identify bills whose stored totals disagree with item totals.
- Take a database backup before the migration.
- Do not modify historical records silently; document and resolve anomalous rows before dropping columns.
- Deploy the backend migration and updated clients as one coordinated release because the API change is breaking.

## Acceptance Criteria

- `bills` contains `referred_amount` and `system_amount`, but not `bill_amount`.
- `bill_items` contains `referred_amount` and `system_amount`, but not `bill_amount`.
- All bill and item totals are calculated as `referred_amount + system_amount`.
- Bill-level referred and system amounts equal the sums of the bill items.
- No backend source code, validation rule, resource, report, print payload, or test references `bill_amount`.
- No `/home/eranda/test/test-f` source or type references `bill_amount`.
- Public API requests and responses use the canonical fields consistently.
- Day summaries, service-cost reports, reception lists, pharmacy workflows, booking workflows, and printed bills remain numerically correct.
- Backend feature tests pass with coverage for zero amounts, multiple items, medicine items, public API flows, and inconsistent legacy data handling.
- Frontend lint/build checks pass.
- `composer audit` and the application test suite pass after implementation.

## Verification Commands

```bash
rg -n --hidden -S "bill_amount" app routes database tests
rg -n --hidden -S "bill_amount" /home/eranda/test/test-f --glob '!node_modules/**' --glob '!*.lock'
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

From `/home/eranda/test/test-f`:

```bash
npm run lint
npm run build
```

## Notes

- Existing code currently uses `bill_amount` inconsistently: some flows treat it as the referred portion, while public billing code treats it as the combined amount and derives `referred_amount` from it.
- The day-summary and service-cost reports currently sum `bill_items.bill_amount`; these must be explicitly changed to sum `referred_amount`, `system_amount`, or their calculated total according to the report’s business meaning.
- The migration must be forward-compatible and must not edit the original historical migrations.
