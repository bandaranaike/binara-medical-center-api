<?php

namespace App\Http\Middleware;

use App\Models\TrustedSite;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $apiKey = $request->header('X-API-KEY');
        $referer = parse_url($request->headers->get('referer'), PHP_URL_HOST);

        if (!$apiKey || !$referer) {
            return new JsonResponse('Please provide a valid API key. Unauthorized for ' . $referer, 403);
        }

        $trustedSite = TrustedSite::where('domain', $referer)->where('api_key', $apiKey)->first();

        if (!$trustedSite) {
            return new JsonResponse('Invalid API Key for ' . $referer, 403);
        }

        return $next($request);
    }
}
