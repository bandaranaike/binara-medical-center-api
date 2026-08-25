# Today List Backend Requirements

## Purpose

The reception **Today List** screen must be able to load pending bills for any calendar date selected by the user. The screen uses the `Asia/Colombo` timezone.

## Endpoint contract

Update `GET /bills/pending/reception` to accept an optional `date` query parameter:

```text
GET /bills/pending/reception?date=YYYY-MM-DD
```

- `date` is a calendar date in `Asia/Colombo`, not a UTC date/time.
- Validate the value strictly as `YYYY-MM-DD` and return HTTP 422 with a useful validation error when it is invalid.
- If `date` is omitted, default to the current calendar date in `Asia/Colombo` (not the application server's local timezone).
- Return only the pending reception bills whose queue/appointment date falls on that calendar date.
- Preserve the existing response shape and authorization rules.

## Date filtering

Use a half-open UTC range derived from the selected Colombo date:

```text
start = 00:00:00 Asia/Colombo on YYYY-MM-DD
end   = 00:00:00 Asia/Colombo on the following day
```

Query with `>= start` and `< end`. Do not compare a UTC timestamp directly with the `YYYY-MM-DD` string, and do not use the database/server timezone implicitly. This prevents records near midnight from appearing under the wrong date.

Apply the range to the same queue/appointment datetime column currently used to determine the existing “today” list. Keep existing pending/status and reception-scope filters unchanged.

## Response timestamps

Return datetime fields, including `queue_date` when it contains a time, as ISO-8601 UTC timestamps with an explicit `Z` suffix, for example:

```json
{
  "queue_date": "2026-08-25T06:30:00Z"
}
```

Do not return timezone-less datetime strings such as `2026-08-25 12:00:00`; they are ambiguous to browsers and clients. The frontend converts the UTC timestamp to `Asia/Colombo` for display.

If `queue_date` is intentionally a date-only field, return it as `YYYY-MM-DD` and document it as date-only. Do not append a fake time or timezone to a date-only value.

## Compatibility and tests

Add or update automated tests covering:

1. No `date` parameter defaults to today's Colombo date.
2. A valid `date` returns only records in that Colombo calendar day.
3. Records immediately before and after Colombo midnight are assigned to the correct date.
4. Invalid dates return HTTP 422.
5. Existing status, authorization, and response fields remain unchanged.
6. Serialized datetime values include an explicit UTC offset/`Z`.
