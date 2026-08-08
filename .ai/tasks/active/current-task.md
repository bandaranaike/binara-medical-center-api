# Current Task

## Title

Add receptionist-safe OPD service autocomplete for the Electron billing desk

## ID

TASK-128

## Status

Completed

## Goal

Provide a public app-token endpoint that lets reception search active services, including OPD services, without staff login or admin-only authorization.

## Implementation

- kept the `verify.apikey` and `public.app.token` middleware chain on `/api/public/services/search`
- normalized and validated the search query, including the two-character minimum
- matched names and keys case-insensitively
- ranked exact and prefix matches before contains matches
- applied the public service-type filter before limiting results to eight
- returned an empty `data` collection with HTTP `200` when there are no matches
- documented the endpoint and added OPD, result-limit, and no-match feature coverage

## Verification

- `tests/Feature/Public/PublicApiTest.php`
