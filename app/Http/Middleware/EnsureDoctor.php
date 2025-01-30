<?php

namespace App\Http\Middleware;

use App\Models\Doctor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctor
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();

        if (!$doctor) {
            return response()->json(['message' => 'There is no doctor assigned to the logged-in user'], Response::HTTP_NOT_FOUND);
        }

        $request->merge(['doctor_id' => $doctor->id]);

        return $next($request);
    }
}
