<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

//        dd($user, $roles);

        if (!$user || !$user->hasRole($roles)) {
            return response()->json([
                'message' => 'Unauthorized: You do not have permission to make this request.',
            ], 403);
        }

        return $next($request);
    }
}
