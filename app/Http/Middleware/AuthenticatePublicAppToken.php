<?php

namespace App\Http\Middleware;

use App\Models\PublicAppToken;
use App\Models\TrustedSite;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePublicAppToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();

        if (! $plainTextToken) {
            return new JsonResponse([
                'message' => 'Unauthorized: Missing public application token.',
            ], 401);
        }

        /** @var TrustedSite|null $trustedSite */
        $trustedSite = $request->attributes->get('trusted_site');

        if (! $trustedSite instanceof TrustedSite) {
            return new JsonResponse([
                'message' => 'Forbidden: Trusted site context was not resolved.',
            ], 403);
        }

        $publicAppToken = PublicAppToken::query()
            ->where('trusted_site_id', $trustedSite->id)
            ->where('token_hash', hash('sha256', $plainTextToken))
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $publicAppToken) {
            return new JsonResponse([
                'message' => 'Unauthorized: Invalid public application token.',
            ], 401);
        }

        $publicAppToken->forceFill([
            'last_used_at' => now(),
        ])->save();

        $request->attributes->set('public_app_token', $publicAppToken);

        return $next($request);
    }
}
