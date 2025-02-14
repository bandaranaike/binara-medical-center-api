<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatient
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $patients = Patient::where('user_id', Auth::id())->get();

        if ($patients->isEmpty()) {
            return response()->json(['message' => 'There are no patients assigned to the logged-in user'], Response::HTTP_NOT_FOUND);
        }

        $request->merge(['ensure_middleware_patient_ids' => $patients->pluck('id')]);

        return $next($request);
    }
}
