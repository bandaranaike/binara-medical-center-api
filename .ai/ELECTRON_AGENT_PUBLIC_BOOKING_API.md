# Electron AI Agent: Public Booking API

This note is for an AI agent running inside the Electron app that needs to create a booking through the public API.

## Endpoint

- Local base URL: `http://localhost/test-b.local`
- Booking endpoint: `POST /api/public/bookings/make-appointment`

Full local URL:

```text
http://localhost/test-b.local/api/public/bookings/make-appointment
```

## Required headers

Every request must include both trusted-site headers and the public bearer token:

```http
Accept: application/json
Content-Type: application/json
X-API-KEY: <trusted-site-api-key>
Referer: https://<trusted-site-domain>
Authorization: Bearer <public-app-token>
```

## Request body

Send JSON with these fields:

```json
{
  "name": "John Doe",
  "phone": "0771234567",
  "email": "john@example.com",
  "age": 30,
  "doctor_id": 12,
  "doctor_type": "specialist",
  "date": "2026-03-27"
}
```

## Field rules

- `name`: required string
- `phone`: required when `user_id` is not sent
- `email`: optional valid email
- `age`: required number between `0` and `100`
- `doctor_id`: required existing doctor ID
- `doctor_type`: required, allowed values: `specialist`, `dental`
- `date`: required date string in `YYYY-MM-DD`
- `user_id`: optional existing user UUID if the booking should be linked to an existing user

## Important behavior

- The phone number must already be verified in the backend OTP flow.
- The API reduces available seats for the selected doctor and date.
- If the patient does not exist, the API creates a patient/user record automatically.
- The API blocks duplicate bookings for the same patient, doctor, and date.
- A bill, bill item, and daily queue entry are created as part of the booking flow.

## Success response

Status: `200 OK`

```json
{
  "doctor_name": "Dr. Public Booking",
  "doctor_specialty": "Cardiology",
  "booking_number": 1,
  "date": "2026-03-27",
  "reference": "6b4c7fcb-1d0b-4e4f-a53d-0e3d4708a111",
  "generated_at": "2026-03-26T10:15:30.000000Z",
  "bill_id": 45
}
```

## Common error responses

- `401 Unauthorized`
  - missing or invalid bearer token
- `403 Forbidden`
  - missing or invalid `X-API-KEY` / `Referer`
- `422 Unprocessable Entity`
  - phone number not verified
  - doctor schedule is full
  - duplicate appointment already exists
  - validation failure

## Agent guidance

- First search or fetch the correct `doctor_id` before attempting a booking.
- Do not guess `doctor_type`; send the value returned by the doctor list API.
- If the API returns `422` with a phone-verification message, ask the user to complete OTP verification first.
- If the API returns a duplicate-booking message, show the existing booking context instead of retrying.
- Persist `reference`, `bill_id`, and `booking_number` in the Electron flow for follow-up steps.

## Minimal Electron fetch example

```ts
const response = await fetch('http://localhost/test-b.local/api/public/bookings/make-appointment', {
  method: 'POST',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-API-KEY': process.env.PUBLIC_API_KEY ?? '',
    Referer: 'https://desktop.local',
    Authorization: `Bearer ${process.env.PUBLIC_APP_TOKEN ?? ''}`,
  },
  body: JSON.stringify({
    name: 'John Doe',
    phone: '0771234567',
    email: 'john@example.com',
    age: 30,
    doctor_id: 12,
    doctor_type: 'specialist',
    date: '2026-03-27',
  }),
});

const payload = await response.json();

if (!response.ok) {
  throw new Error(typeof payload === 'string' ? payload : payload.message ?? 'Booking failed');
}
```
