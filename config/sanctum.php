<?php

use Laravel\Sanctum\Sanctum;

$statefulDomains = array_filter(array_map(
    static fn (?string $domain): ?string => $domain !== null && $domain !== "" ? $domain : null,
    [
        "localhost",
        "localhost:3000",
        "localhost:3001",
        "127.0.0.1",
        "127.0.0.1:8000",
        "::1",
        Sanctum::currentApplicationUrlWithPort(),
        env("FRONTEND_URL"),
        env("WEBSITE_URL"),
        env("SANCTUM_STATEFUL_DOMAINS"),
    ]
));

$expandedStatefulDomains = [];

foreach ($statefulDomains as $domain) {
    foreach (explode(",", $domain) as $value) {
        $trimmedValue = trim($value);

        if ($trimmedValue === "") {
            continue;
        }

        $expandedStatefulDomains[] = $trimmedValue;

        $host = parse_url($trimmedValue, PHP_URL_HOST);
        $port = parse_url($trimmedValue, PHP_URL_PORT);

        if (is_string($host) && $host !== "") {
            $expandedStatefulDomains[] = $host;

            if ($port !== null) {
                $expandedStatefulDomains[] = $host.":".$port;
            }
        }
    }
}

return [
    "stateful" => array_values(array_unique($expandedStatefulDomains)),
    "guard" => ["web"],
    "expiration" => null,
    "token_prefix" => env("SANCTUM_TOKEN_PREFIX", ""),
    "middleware" => [
        "authenticate_session" => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        "encrypt_cookies" => Illuminate\Cookie\Middleware\EncryptCookies::class,
        "validate_csrf_token" => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
