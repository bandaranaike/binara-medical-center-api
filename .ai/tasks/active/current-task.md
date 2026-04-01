# Current Task

## Title

Add public day-summary API for the Electron app

## Status

Completed

## Goal

Expose the day-summary report through the public app-token API in `routes/public.php` so the Electron app can fetch printer-ready summary data.

## Work items

- extract the aggregation into a shared service
- add a public controller and route under `/api/public/*`
- keep the response contract identical to the existing day-summary output
- update public API docs for the Electron agent
- cover the public route with feature tests

## Notes

- public route should use app-token auth, not Sanctum staff auth
- keep `date` optional and `shift` required
- implemented at `GET /api/public/reports/day-summary`
