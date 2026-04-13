# Current Task

## Title

Add holiday CRUD API endpoints

## Status

Completed

## Goal

Provide full CRUD support for holidays through `app/Http/Controllers/HolidayController.php` and `routes/api.php`.

## Work items

- add store, index, show, update, and destroy behavior for holidays
- add request validation for holiday payloads
- expose admin CRUD endpoints in `routes/api.php`
- cover the holiday API with feature tests

## Notes

- keep `GET /api/holidays/today-status` public inside the `verify.apikey` group
- keep holiday CRUD admin-only to match other lookup-style resources
