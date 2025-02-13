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
        $patient = Patient::where('user_id', Auth::id())->first();

        if (!$patient) {
            return response()->json(['message' => 'There is no patient assigned to the logged-in user'], Response::HTTP_NOT_FOUND);
        }
        Log::info("Patient Id: " . $patient->id);
        Log::warning("Auth id: " . Auth::id());
        $request->merge(['ensure_middleware_patient_id' => $patient->id]);

        return $next($request);
    }
}
