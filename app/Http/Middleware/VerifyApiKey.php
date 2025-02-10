<?php

namespace App\Http\Middleware;

use App\Models\TrustedSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $referer = parse_url($request->headers->get('referer'), PHP_URL_HOST);

        if (!$apiKey || !$referer) {
            return response()->json('Unauthorized' . $referer, 404);
        }

        $trustedSite = TrustedSite::where('domain', $referer)->where('api_key', $apiKey)->first();

        if (!$trustedSite) {
            return response()->json('Invalid API Key' . $referer, 405);
        }

        return $next($request);
    }
}
