<?php

namespace App\Http\Controllers;

use App\Enums\DoctorAvailabilityStatus;
use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDoctorAvailabilityRequest;
use App\Http\Requests\UpdateDoctorAvailabilityRequest;
use App\Http\Resources\DoctorAvailabilityResource;
use App\Http\Resources\TodayAvailableDoctorsResource;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorAvailabilityController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = new DoctorAvailability();
        $this->updateRequest = new UpdateDoctorAvailabilityRequest();
        $this->storeRequest = new StoreDoctorAvailabilityRequest();
        $this->resource = DoctorAvailabilityResource::class;
        $this->relationships = ['doctor:id,name'];
    }

    public function searchDoctor(Request $request): JsonResponse
    {
        $searchQuery = $request->query('query');

        $doctors = Doctor::select(['doctors.id', 'doctors.name', 'specialties.name as specialty_name'])
            ->join('doctor_availabilities', function ($join) use ($request) {
                $join->on('doctors.id', '=', 'doctor_availabilities.doctor_id')
                    ->whereBetween('doctor_availabilities.date', $this->getDateRange($request));
            })
            ->join('specialties', 'doctors.specialty_id', '=', 'specialties.id')
            ->where('doctors.name', 'LIKE', "%$searchQuery%")
            ->orWhere('specialties.name', 'LIKE', "%$searchQuery%")
            ->groupBy('doctors.id') // Group by doctor and specialty
            ->limit(10)
            ->get();

        return new JsonResponse($doctors);
    }

    public function searchDoctorsForWebBooking(Request $request): JsonResponse
    {
        $searchQuery = $request->query('query');
        $operator = $searchQuery ? '>=' : '=';
        $date = Carbon::parse($request->query('date', date('Y-m-d')))->format('Y-m-d');


        $doctors = Doctor::select(['doctors.id', 'doctors.name'])
            ->join('doctor_availabilities', function ($join) use ($request, $date, $operator) {
                $join->on('doctors.id', '=', 'doctor_availabilities.doctor_id')
                    ->where('doctor_availabilities.date', $operator, $date);
            })
            ->where('doctors.name', 'LIKE', "%$searchQuery%")
            ->where('doctors.doctor_type', $request->get('type'))
            ->groupBy('doctors.id') // Group by doctor and specialty
            ->limit(10)
            ->get();

        return new JsonResponse($doctors);
    }

    public function getDatesForDoctor($doctorId): JsonResponse
    {
        $doctorAvailability = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('date', '>=', date('Y-m-d'))
            ->orderBy('date', 'ASC')
            ->get(['date']);
        return new JsonResponse($doctorAvailability);
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

        $query = DoctorAvailability::with('doctor.specialty:id,name')
            ->with('doctor:id,name,doctor_type,specialty_id')
            ->where('status', DoctorAvailabilityStatus::ACTIVE);

        // Determine the date range
        [$startDate, $endDate] = $this->getDateRange($request);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Filter by selected doctors (if provided)
        if ($request->has('doctor_ids')) {
            $query->whereIn('doctor_id', $request->doctor_ids);
        }

        $availabilities = $query->orderBy('date')->orderBy('time')->get();

        return new JsonResponse($availabilities);
    }

    public function getTodayAvailableDoctorsForWeb(): JsonResponse
    {
        $availableDoctors = DoctorAvailability::with('doctor.specialty:id,name')
            ->with('doctor:id,name,specialty_id,doctor_type')
            ->where('date', date('Y-m-d'))
            ->orderBy('time')
            ->get(['id', 'doctor_id', 'time', 'seats', 'available_seats']);

        return new JsonResponse(TodayAvailableDoctorsResource::collection($availableDoctors));
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
