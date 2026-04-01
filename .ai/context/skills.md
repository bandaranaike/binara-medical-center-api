# Skills Reference

This file explains which Codex skills to activate for this project and when to use them.

## Core rule

Activate the relevant skill as soon as the task enters that domain. Do not wait until implementation is already underway.

## Available skills

### `laravel-best-practices`

Use for backend work in:

- `app/**`
- `routes/api.php`
- `routes/public.php`
- `database/**`
- `tests/**`

Use it for:

- controllers
- models and relationships
- form requests
- migrations
- services
- jobs and commands
- policies and authorization
- Eloquent query optimization
- refactors and code reviews

Why it matters here:

- this codebase relies heavily on controller and trait composition
- business logic spans billing, doctor scheduling, stock, and patient history
- role checks, Sanctum auth, and relational integrity are central to correctness

### `inertia-react-development`

Use only when the task touches the Inertia and React frontend:

- `resources/js/**`
- `routes/web.php`
- Inertia pages, layouts, auth screens, and navigation

Why it matters here:

- the repository includes Breeze and Inertia scaffolding
- the main product is API-first, but the web auth and profile surface still exists
- backend-first assumptions should not leak into React work

### `tailwindcss-development`

Use for any Tailwind or UI class work:

- Blade or JSX markup styling
- layout changes
- components in `resources/js/**`

Why it matters here:

- Tailwind v3 is installed
- it is relevant to the small frontend surface, not to API controllers

## Recommended activation rules

- Use `laravel-best-practices` by default for almost all backend tasks.
- Add `inertia-react-development` only when React or Inertia behavior is involved.
- Add `tailwindcss-development` only when markup or styling changes are involved.

## Project-specific guidance

- Treat this repository as a Laravel 11 API with a smaller Inertia and Breeze surface.
- Check `routes/api.php` before assuming endpoint behavior.
- Check `routes/public.php` when the task mentions Electron, desktop billing, or public bearer-token access.
- Check `bootstrap/app.php` before changing auth behavior.
- Reuse shared infrastructure before adding new abstractions:
  - `CrudTrait`
  - billing, stock, queue, printing, and pricing traits
  - `DoctorScheduleService`
- Expect role-sensitive behavior. Common roles are:
  - `admin`
  - `reception`
  - `doctor`
  - `pharmacy`
  - `pharmacy_admin`
  - `nurse`
  - `patient`

## Code entry points worth checking

- `AGENTS.md`
- `.ai/README.md`
- `.ai/context/architecture.md`
- `.ai/context/functionality.md`
- `.ai/context/database-schema.md`
- `bootstrap/app.php`
- `routes/api.php`
- `routes/public.php`
- `app/Http/Middleware/*`
- `app/Http/Controllers/*`
- `app/Http/Controllers/PublicApi/*`
- `app/Http/Controllers/Traits/*`
- `app/Services/DoctorScheduleService.php`
- `app/Models/*`
- `database/migrations/*`
- `tests/Feature/*`
