# Electron Day Summary Agent Guide

This is a `domains/public-api/` reference note for an AI agent running inside the Electron app that needs to print the daily service summary.

## Purpose

Use this endpoint when the desktop app needs printer-ready service totals for one shift on one day.

The backend already returns the payload shape expected by the Windows-side `print_summary.py` script.

## Endpoint

- Local base URL: `http://localhost/test-b.local`
- Day summary endpoint: `GET /api/public/reports/day-summary`

Full local URL:

```text
http://localhost/test-b.local/api/public/reports/day-summary
```

## Authentication

This is a `/api/public/*` bearer-token endpoint.

It uses the Electron public API path:

- `verify.apikey`
- `public.app.token`

### Required headers

```http
Accept: application/json
Content-Type: application/json
X-API-KEY: <trusted-site-api-key>
Referer: https://<trusted-site-domain>
Authorization: Bearer <public-app-token>
```

### Auth requirements

- the token must be a valid public app bearer token
- no staff login is required
- no Sanctum user token is required

## Query parameters

- `shift` required
  - allowed values:
    - `morning`
    - `evening`
- `date` optional
  - format: `YYYY-MM-DD`
  - defaults to today in the backend if omitted

## Request examples

### Selected date and shift

```text
GET /api/public/reports/day-summary?date=2026-04-01&shift=morning
```

### Today for evening shift

```text
GET /api/public/reports/day-summary?shift=evening
```

## Response shape

Status: `200 OK`

```json
{
  "start_date": "2026-04-01",
  "end_date": "2026-04-01",
  "items": [
    {
      "service_name": "Channeling Dr.Aruna",
      "quantity": 6,
      "total": 12000
    },
    {
      "service_name": "Dressing",
      "quantity": 12,
      "total": 4800
    }
  ]
}
```

## Field meaning

- `start_date`
  - selected report date
- `end_date`
  - same as `start_date` because this report is one-day only
- `items`
  - printable service summary rows
- `items[].service_name`
  - service label to print
- `items[].quantity`
  - count of matching `bill_items` rows for that service label
- `items[].total`
  - sum of `bill_items.bill_amount`

## Backend behavior

- filters by `bills.date`
- filters by selected `shift`
- includes only paid bills
- excludes soft-deleted bills
- excludes rows where the summed total is `0`
- for `channeling`, builds the label as:
  - `services.name + " " + doctors.name`
- for all other services, uses:
  - `services.name`

## Common error responses

- `401 Unauthorized`
  - missing or invalid public app bearer token
- `403 Forbidden`
  - missing or invalid trusted-site headers
- `422 Unprocessable Entity`
  - missing `shift`
  - invalid `shift`
  - invalid `date` format

## Agent guidance

- Call this route with the Electron public bearer token.
- Always send `shift`.
- Send `date` explicitly when reprinting an older summary.
- Pass the response body directly to the Python print service without reshaping field names.
- Treat `items: []` as a valid “no sales for this date/shift” result.

## Minimal Electron fetch example

```ts
const response = await fetch(
  'http://localhost/test-b.local/api/public/reports/day-summary?date=2026-04-01&shift=morning',
  {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-API-KEY': process.env.API_KEY ?? '',
      Referer: 'https://desktop.local',
      Authorization: `Bearer ${process.env.PUBLIC_APP_TOKEN ?? ''}`,
    },
  },
);

const payload = await response.json();

if (!response.ok) {
  throw new Error(payload.message ?? 'Failed to load day summary');
}

await fetch('http://127.0.0.1:8000/print-summary', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify(payload),
});
```
