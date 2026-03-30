# Booking And Date-Driven API Contracts

This document defines the backend API contracts needed before starting the next renderer tasks.

It is written for backend implementation work and prefers reusing the current bill/booking workflow instead of creating parallel concepts unless the existing backend structure cannot support it cleanly.

## Implementation preference

If the backend already stores bookings as bills with `status = booked`, use that model.

Preferred behavior:

- booking list = bills filtered by `status = booked` and selected `date`
- edit booking = update that booked bill, related patient details, and bill items
- delete booking = delete the booked bill
- proceed to payment = move the booking bill from `booked` to the next workflow state such as `doctor` or `reception`

This is preferable to creating a separate bookings table unless one already exists and is clearly the source of truth.

## 1. Date-specific doctor list

Use this when the selected billing date changes and the available doctor list must refresh.

### Endpoint

`GET /api/public/doctors/by-date?date=2026-03-30&type=opd`

### Query params

- `date`: required, format `YYYY-MM-DD`
- `type`: required, one of:
  - `opd`
  - `specialist`
  - `dental`
  - `treatment`

### Success response

```json
{
  "data": [
    {
      "id": 12,
      "name": "Dr. Example",
      "specialty": "Cardiology",
      "telephone": "+94770000000",
      "email": "doctor@example.com",
      "address": "Colombo",
      "doctor_type": "specialist",
      "dental_split_mode": "percentage",
      "dental_split_value": 40,
      "availability_date": "2026-03-30",
      "available_seats": 8
    }
  ]
}
```

### Empty/error response

```json
{
  "message": "No doctors found for the selected date."
}
```

### Notes

Relevant existing routes that may be adapted instead of creating a new one:

- `GET /api/doctor-availabilities/get-today-doctors`
- `GET /api/doctor-availabilities/search-booking-doctors?date=YYYY-MM-DD&type=...`

## 2. Booking list by date

This powers the booking-list tab.

### Endpoint

`GET /api/public/bookings?date=2026-03-30`

### Query params

- `date`: required, format `YYYY-MM-DD`
- `search`: optional
- `doctor_id`: optional
- `page`: optional
- `per_page`: optional

### Success response

```json
{
  "data": [
    {
      "id": 481,
      "bill_id": 481,
      "reference": "BKG-20260330-001",
      "booking_number": 10,
      "date": "2026-03-30",
      "status": "booked",
      "patient": {
        "id": 33,
        "name": "Patient Name",
        "telephone": "+94770000000",
        "email": "patient@example.com",
        "age": 30,
        "gender": "male",
        "address": "Colombo",
        "birthday": "1996-02-14"
      },
      "doctor": {
        "id": 12,
        "name": "Dr. Example",
        "specialty": "Cardiology",
        "doctor_type": "specialist"
      },
      "payment_type": "cash",
      "shift": "morning",
      "bill_amount": 2500,
      "system_amount": 0,
      "items": [
        {
          "name": "Consultation",
          "price": "2000"
        },
        {
          "name": "Booking Fee",
          "price": "500"
        }
      ],
      "created_at": "2026-03-30T09:30:00Z"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1
  }
}
```

### Notes

There is already a documented backend route that may be reused/adapted:

- `GET /api/bills/bookings/{time?}`

If that route is retained, the backend may add a date filter or a new public/app-safe variant with the same response shape.

## 3. Get single booking for edit

Use this if the booking list row does not already contain enough data to hydrate the billing form.

### Endpoint

`GET /api/public/bookings/{id}`

### Success response

```json
{
  "id": 481,
  "bill_id": 481,
  "reference": "BKG-20260330-001",
  "booking_number": 10,
  "date": "2026-03-30",
  "status": "booked",
  "patient": {
    "id": 33,
    "name": "Patient Name",
    "telephone": "+94770000000",
    "email": "patient@example.com",
    "age": 30,
    "gender": "male",
    "address": "Colombo",
    "birthday": "1996-02-14",
    "registration_no": "REG-001"
  },
  "doctor": {
    "id": 12,
    "name": "Dr. Example",
    "specialty": "Cardiology",
    "doctor_type": "specialist"
  },
  "payment_type": "cash",
  "shift": "morning",
  "service_type": "specialist",
  "bill_amount": 2500,
  "system_amount": 0,
  "items": [
    {
      "name": "Consultation",
      "price": "2000"
    },
    {
      "name": "Booking Fee",
      "price": "500"
    }
  ]
}
```

## 4. Update booking

Used for the `Edit` action.

### Endpoint

`PUT /api/public/bookings/{id}`

### Request body

```json
{
  "patient": {
    "name": "Patient Name",
    "telephone": "+94770000000",
    "email": "patient@example.com",
    "age": 30,
    "gender": "male",
    "address": "Colombo",
    "birthday": "1996-02-14",
    "registration_no": "REG-001"
  },
  "doctor_id": 12,
  "doctor_type": "specialist",
  "date": "2026-03-30",
  "shift": "morning",
  "payment_type": "cash",
  "service_type": "specialist",
  "bill_amount": 2500,
  "system_amount": 0,
  "items": [
    {
      "name": "Consultation",
      "price": "2000"
    },
    {
      "name": "Booking Fee",
      "price": "500"
    }
  ]
}
```

### Success response

```json
{
  "message": "Booking updated successfully.",
  "booking": {
    "id": 481,
    "bill_id": 481,
    "reference": "BKG-20260330-001",
    "booking_number": 10,
    "date": "2026-03-30",
    "status": "booked"
  }
}
```

### Validation error response

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "doctor_id": [
      "The selected doctor is not available on the selected date."
    ]
  }
}
```

## 5. Delete booking

Used for the `Delete` action.

### Endpoint

`DELETE /api/public/bookings/{id}`

### Request body

No request body.

### Success response

```json
{
  "message": "Booking deleted successfully.",
  "deleted_id": 481
}
```

### Conflict response

```json
{
  "message": "Only bookings in booked status can be deleted."
}
```

## 6. Proceed to payment

Used for the `Proceed to payment` action.

This should convert the booking into the normal bill workflow and remove it from the booking list by moving it out of `booked` status.

### Endpoint

`POST /api/public/bookings/{id}/proceed-to-payment`

### Request body

```json
{
  "payment_type": "cash",
  "shift": "morning",
  "bill_amount": 2500,
  "system_amount": 0,
  "items": [
    {
      "name": "Consultation",
      "price": "2000"
    },
    {
      "name": "Booking Fee",
      "price": "500"
    }
  ]
}
```

### Success response

```json
{
  "message": "Booking moved to payment successfully.",
  "bill": {
    "id": 481,
    "reference": "BKG-20260330-001",
    "status": "doctor",
    "payment_type": "cash",
    "bill_amount": 2500,
    "system_amount": 0,
    "date": "2026-03-30"
  }
}
```

### Conflict response

```json
{
  "message": "This booking has already been processed."
}
```

## Frontend-friendly field shapes

For easier Electron integration, these shapes should be preserved in responses.

### Doctor row shape

```json
{
  "id": 12,
  "name": "Dr. Example",
  "specialty": "Cardiology",
  "telephone": "+94770000000",
  "email": "doctor@example.com",
  "address": "Colombo",
  "doctor_type": "specialist",
  "dental_split_mode": "percentage",
  "dental_split_value": 40
}
```

### Lightweight booking row shape

```json
{
  "id": 481,
  "bill_id": 481,
  "reference": "BKG-20260330-001",
  "booking_number": 10,
  "date": "2026-03-30",
  "doctor_name": "Dr. Example",
  "doctor_specialty": "Cardiology"
}
```

## Suggested backend approach

1. Reuse existing bill/booking workflow if bookings are already represented as bills with `status = booked`.
2. Reuse existing doctor availability routes if they can return date-filtered doctors by operation type.
3. Only introduce brand-new endpoints when the existing routes cannot provide the correct auth model or response shape for the Electron app.
