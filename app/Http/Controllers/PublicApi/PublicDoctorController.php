<?php

namespace App\Http\Controllers\PublicApi;

use App\Enums\DoctorAvailabilityStatus;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicDoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_type' => ['nullable', 'string', 'in:opd,specialist,dental,treatment'],
            'search' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'sort' => ['nullable', 'array'],
            'sort.*' => ['string'],
        ]);

        $date = Carbon::parse($validated['date'] ?? now()->toDateString())->toDateString();

        $query = Doctor::query()
            ->select([
                'doctors.id',
                'doctors.name',
                'doctors.telephone',
                'doctors.email',
                'doctors.doctor_type',
                'specialties.name as specialty_name',
            ])
            ->join('doctor_availabilities', function ($join) use ($date) {
                $join->on('doctors.id', '=', 'doctor_availabilities.doctor_id')
                    ->where('doctor_availabilities.date', '=', $date)
                    ->where('doctor_availabilities.status', '=', DoctorAvailabilityStatus::ACTIVE->value)
                    ->where('doctor_availabilities.available_seats', '>', 0);
            })
            ->leftJoin('specialties', 'doctors.specialty_id', '=', 'specialties.id')
            ->groupBy(
                'doctors.id',
                'doctors.name',
                'doctors.telephone',
                'doctors.email',
                'doctors.doctor_type',
                'specialties.name',
            );

        if (! empty($validated['doctor_type'])) {
            $query->where('doctors.doctor_type', $validated['doctor_type']);
        }

        if (! empty($validated['search'])) {
            $query->where('doctors.name', 'like', '%'.$validated['search'].'%');
        }

        foreach ($validated['sort'] ?? [] as $sort) {
            [$field, $direction] = array_pad(explode(':', $sort, 2), 2, 'asc');

            if ($field === 'name') {
                $query->orderBy('doctors.name', strtolower($direction) === 'desc' ? 'desc' : 'asc');
            }
        }

        if (empty($validated['sort'])) {
            $query->orderBy('doctors.name');
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }
}
