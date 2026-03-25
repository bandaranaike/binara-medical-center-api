# Project Skills Reference

This project already defines reusable Codex skills. Use them deliberately so future tasks start from the right constraints and conventions.

## Available skills

### `laravel-best-practices`

Use for any Laravel backend work:

- controllers
- models and relationships
- form requests
- migrations
- services
- jobs / commands
- policies / authorization
- Eloquent query optimization
- refactors and code reviews

Why it matters here:

- this API relies heavily on controller + trait composition
- business logic touches billing, doctor scheduling, stock, and patient history
- role checks, Sanctum auth, and table relationships are central to correctness

### `inertia-react-development`

Use only when the task touches the existing Inertia / React frontend:

- `resources/js/**`
- `routes/web.php`
- Inertia pages, layouts, auth screens, navigation

Why it matters here:

- the repository contains Breeze / Inertia scaffolding
- the main product appears API-first, but frontend auth/profile pages still exist
- avoid using Inertia-specific assumptions for API-only work

### `tailwindcss-development`

Use for any Tailwind or UI class work:

- Blade / JSX markup styling
- layout changes
- components in `resources/js/**`

Why it matters here:

- Tailwind v3 is installed
- this is only relevant for the small frontend portion, not the API controllers

## Recommended activation rules for this codebase

- Use `laravel-best-practices` by default for almost every backend task in `app/`, `routes/api.php`, `database/`, and `tests/`.
- Add `inertia-react-development` only if the task touches React pages or web auth/profile flows.
- Add `tailwindcss-development` only if the task changes UI markup or styling.

## Project-specific guidance for future tasks

- Treat this repository as a Laravel 11 API with a small Inertia/Breeze surface, not as a frontend-first app.
- Check `routes/api.php` before assuming endpoint behavior. The route file is the clearest product map.
- Check `routes/public.php` as well when the task mentions Electron, desktop billing, or public bearer-token access.
- Check middleware in `bootstrap/app.php` before changing auth behavior.
- Reuse shared controller infrastructure where possible:
  - `CrudTrait`
  - billing / stock / queue / printing / pricing traits
  - `DoctorScheduleService`
- Expect role-sensitive behavior. Common roles are:
  - `admin`
  - `reception`
  - `doctor`
  - `pharmacy`
  - `pharmacy_admin`
  - `nurse`
  - `patient`

## Primary files to inspect when starting work

- `AGENTS.md`
- `bootstrap/app.php`
- `routes/api.php`
- `app/Http/Middleware/*`
- `app/Http/Controllers/*`
- `app/Http/Controllers/PublicApi/*`
- `app/Http/Controllers/Traits/*`
- `app/Http/Middleware/AuthenticatePublicAppToken.php`
- `app/Services/DoctorScheduleService.php`
- `app/Models/*`
- `database/migrations/*`
- `tests/Feature/*`
