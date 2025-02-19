<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorAvailabilityRequest;
use App\Http\Requests\UpdateDoctorAvailabilityRequest;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorAvailabilityController extends Controller
{
    public function searchDoctor(Request $request)
    {
        $searchQuery = $request->query('query');

        $doctors = Doctor::select(['doctors.id', 'doctors.name', 'specialties.name as specialty_name'])
            ->join('doctor_availabilities', function ($join) use ($request) {
                $join->on('doctors.id', '=', 'doctor_availabilities.doctor_id')
                    ->whereBetween('doctor_availabilities.date', $this->getDateRange($request));
            })
            ->join('specialties', 'doctors.specialty_id', '=', 'specialties.id')
            ->where('doctors.name', 'LIKE', "%{$searchQuery}%")
            ->orWhere('specialties.name', 'LIKE', "%{$searchQuery}%")
            ->groupBy('doctors.id') // Group by doctor and specialty
            ->limit(10)
            ->get();

//        dd($doctors->toSql());

        return response()->json($doctors);
    }

    public function getAvailability(Request $request): JsonResponse
    {
        // Validate incoming request parameters
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'doctor_ids' => 'nullable|array',
            'doctor_ids.*' => 'integer|exists:doctors,id',
        ]);

        $query = DoctorAvailability::with('doctor:id,name');

        // Determine the date range
        [$startDate, $endDate] = $this->getDateRange($request);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Query availability data

        // Filter by selected doctors (if provided)
        if ($request->has('doctor_ids')) {
            $query->whereIn('doctor_id', $request->doctor_ids);
        }

        $availabilities = $query->orderBy('date')->orderBy('time')->get();

        return response()->json($availabilities);
    }

    private function getDateRange(Request $request): array
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
        } elseif ($request->has('year') && $request->has('month')) {
            $year = $request->year;
            $month = $request->month;
            $startDate = date("$year-$month-01");
            $endDate = date("Y-m-t", strtotime($startDate)); // Get last day of the month
        } else {
            return [];
        }

        return [$startDate, $endDate];
    }
}
