<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\JsonResponse;

class HolidayController extends Controller
{
    public function todayStatus(): JsonResponse
    {
        $today = now()->toDateString();

        $holiday = Holiday::query()
            ->whereDate('date', $today)
            ->where('is_closed', true)
            ->first();

        return response()->json([
            'date' => $today,
            'is_closed' => (bool) $holiday,
            'holiday_name' => $holiday?->name,
            'message' => $holiday?->message,
        ]);
    }
}
