<?php

namespace App\Http\Middleware;

use App\Models\TrustedSite;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        Log::info('Request Headers: ');
        $apiKey = $request->header('X-API-KEY');
        $referer = parse_url($request->headers->get('referer'), PHP_URL_HOST);

        Log::info('Referer: '.$referer);

        if (! $apiKey || ! $referer) {
            return new JsonResponse('Please provide a valid API key. Unauthorized for '.$referer, 403);
        }

        $trustedSite = TrustedSite::where('domain', $referer)->where('api_key', $apiKey)->first();

        if (! $trustedSite) {
            return new JsonResponse('Invalid API Key for '.$referer, 403);
        }

        $request->attributes->set('trusted_site', $trustedSite);

        return $next($request);
    }
}
