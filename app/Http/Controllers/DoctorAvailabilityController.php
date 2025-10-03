<?php

namespace App\Http\Controllers;

use App\Enums\DoctorAvailabilityStatus;
use App\Enums\DoctorScheduleStatus;
use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDoctorAvailabilityRequest;
use App\Http\Requests\UpdateDoctorAvailabilityRequest;
use App\Http\Resources\DoctorAvailabilityResource;
use App\Http\Resources\TodayAvailableDoctorsResource;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $query->whereIn('doctor_id', $request->get('doctor_ids'));
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
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
        } elseif ($request->has('year') && $request->has('month')) {
            $year = $request->get('year');
            $month = $request->get('month');
            $startDate = date("$year-$month-01");
            $endDate = date("Y-m-t", strtotime($startDate)); // Get the last day of the month
        } else {
            return [];
        }

        return [$startDate, $endDate];
    }

    public function generateForDoctorForMonth(Request $request): JsonResponse
    {
        $data = $request->validate([
            'override' => ['sometimes', 'boolean'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'id' => ['required', 'integer', 'exists:doctor_schedules,id'],
        ]);

        $doctorId = $data['doctor_id'];
        $id = $data['id'];
        $override = (bool)($data['override'] ?? false);

        // Calculate month range
        $start = Carbon::today();
        $end = $start->copy()->addMonthNoOverflow()->endOfDay();

        // Pull schedule
        $schedule = DoctorSchedule::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->select(['id', 'doctor_id', 'weekday', 'time', 'seats', 'status'])
            ->firstOrFail();

        // === RULE 3 ===
        if ($schedule->status === DoctorScheduleStatus::INACTIVE->value) {
            if ($override) {
                // Delete all availabilities linked to this schedule
                // Find related availabilities first
                $toDelete = DB::table('doctor_availabilities')
                    ->where('doctor_id', $doctorId)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->where('time', $schedule->time)
                    ->pluck('id');  // safer if you have a primary key

                if ($toDelete->isNotEmpty()) {
                    DB::table('doctor_availabilities')
                        ->whereIn('id', $toDelete)
                        ->delete();
                }


                return response()->json([
                    'ok' => true,
                    'message' => 'Inactive schedule: All related availabilities removed.',
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                ]);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Inactive schedule: No changes made.',
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
            ], 400);
        }

        // === Build recurring slots for this month ===
        $rows = [];
        $date = $start->copy()->next($schedule->weekday);

        if ($start->dayOfWeek === (int)$schedule->weekday) {
            $date = $start->copy();
        }

        while ($date->lte($end)) {
            $rows[] = [
                'doctor_id' => $doctorId,
                'date' => $date->toDateString(),
                'time' => $schedule->time,
                'seats' => (int)$schedule->seats,
                'available_seats' => (int)$schedule->seats,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $date->addWeek();
        }

        if (empty($rows)) {
            return response()->json([
                'ok' => true,
                'message' => 'No slots generated for this month.',
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
            ]);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $override, $doctorId, &$inserted, &$updated, &$skipped) {
            if ($override) {
                // === RULE 1 === Delete and regenerate ===
                DB::table('doctor_availabilities')
                    ->where('doctor_id', $doctorId)
                    ->whereIn('date', array_column($rows, 'date'))
                    ->whereIn('time', array_column($rows, 'time'))
                    ->delete();

                foreach (array_chunk($rows, 1000) as $chunk) {
                    DB::table('doctor_availabilities')->insert($chunk);
                    $inserted += count($chunk);
                }
            } else {
                // === RULE 2 === Insert missing only ===
                $keys = collect($rows)->map(fn($r) => [
                    'doctor_id' => $r['doctor_id'],
                    'date' => $r['date'],
                    'time' => $r['time'],
                ]);

                $existing = DB::table('doctor_availabilities')
                    ->where('doctor_id', $doctorId)
                    ->whereIn(DB::raw('(doctor_id, date, time)'), $keys->map(
                        fn($p) => DB::raw("({$p['doctor_id']}, '{$p['date']}', '{$p['time']}')")
                    )->toArray())
                    ->get(['doctor_id', 'date', 'time'])
                    ->map(fn($e) => "$e->doctor_id|$e->date|$e->time")
                    ->toArray();

                $existingSet = array_flip($existing);

                $newRows = array_filter($rows, fn($r) => !isset($existingSet["{$r['doctor_id']}|{$r['date']}|{$r['time']}"])
                );

                $skipped = count($rows) - count($newRows);

                foreach (array_chunk($newRows, 1000) as $chunk) {
                    DB::table('doctor_availabilities')->insert($chunk);
                    $inserted += count($chunk);
                }
            }
        });

        return response()->json([
            'ok' => true,
            'message' => $override
                ? 'Availability regenerated with override.'
                : 'Availability generated, missing ones only.',
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }

}
