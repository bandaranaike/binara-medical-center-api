# Public App Token Status

This is a `domains/public-api/` reference note for the machine-auth token design used by the Electron integration.

## Status

Implemented.

The backend is already capable of issuing long-lived bearer tokens for the Electron desktop application.

## Current design

Electron desktop API access is protected by two layers:

1. trusted site validation
   - `X-API-KEY`
   - `Referer`
   - matched against `trusted_sites`
2. application bearer token validation
   - `Authorization: Bearer <token>`
   - validated against `public_app_tokens`

This does not require:

- staff login
- Sanctum user token login
- browser session cookie
- CSRF cookie flow

## Relevant files

- `app/Console/Commands/CreatePublicApiToken.php`
- `app/Models/PublicAppToken.php`
- `app/Models/TrustedSite.php`
- `app/Http/Middleware/AuthenticatePublicAppToken.php`
- `app/Http/Middleware/VerifyApiKey.php`
- `routes/public.php`
- `routes/api.php`
- `tests/Feature/Public/PublicApiTest.php`

## How to generate a bearer token

Command:

```bash
php artisan public-api:token {trusted_site_id_or_domain} "Electron Desktop"
```

Example:

```bash
php artisan public-api:token desktop.local "Electron Desktop"
```

Optional explicit expiry:

```bash
php artisan public-api:token desktop.local "Electron Desktop" --expires-at="2028-12-31 23:59:59"
```

## Long-lived token behavior

- If `--expires-at` is omitted, the token is created with `expires_at = null`.
- In the current implementation, `null` means no scheduled expiry.
- The token remains valid until revoked or removed.

That matches the Electron requirement for a long-lasting bearer token that does not change regularly.

## Required Electron headers

```http
Accept: application/json
Content-Type: application/json
X-API-KEY: <trusted-site-api-key>
Referer: https://<trusted-site-domain>
Authorization: Bearer <public-app-token>
```

## Public endpoints currently supported

- `GET /api/public/patients/search`
- `POST /api/public/patients`
- `PUT /api/public/patients/{id}`
- `POST /api/public/patients/upsert`
- `GET /api/public/doctors`
- `POST /api/public/bills`

## Operational notes

- The plain-text token is only shown once when the Artisan command runs.
- Store it securely in the Electron app configuration or secure secret storage.
- The database stores only the SHA-256 hash.
- Tokens are scoped to a specific trusted site through `trusted_site_id`.
- `last_used_at` is updated on successful use.
- Revocation is supported via the `revoked_at` column, but there is not yet a dedicated revoke command.

## Recommendation

For the Electron app, use one named long-lived token per deployed desktop environment or per trusted site, for example:

- `Electron Desktop Production`
- `Electron Desktop Staging`

This keeps rotation manageable while avoiding shared anonymous credentials across unrelated environments.
