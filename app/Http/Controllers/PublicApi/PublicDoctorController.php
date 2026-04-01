<?php

namespace App\Http\Controllers\PublicApi;

use App\Enums\DoctorAvailabilityStatus;
use App\Enums\ServiceKey;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicDoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:opd,specialist,dental,treatment'],
            'doctor_type' => ['nullable', 'string', 'in:opd,specialist,dental,treatment'],
            'search' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'sort' => ['nullable', 'array'],
            'sort.*' => ['string'],
        ]);

        $date = Carbon::parse($validated['date'] ?? now()->toDateString())->toDateString();
        $doctorType = $validated['doctor_type'] ?? $validated['type'] ?? null;

        $query = Doctor::query()
            ->select([
                'doctors.id',
                'doctors.name',
                'doctors.telephone',
                'doctors.email',
                'doctors.doctor_type',
                'doctor_availabilities.date as availability_date',
                'doctor_availabilities.available_seats',
                'specialties.name as specialty_name',
            ])
            ->selectRaw('null as address')
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
                'doctor_availabilities.date',
                'doctor_availabilities.available_seats',
                'specialties.name',
            );

        if (! empty($doctorType)) {
            $query->where('doctors.doctor_type', $doctorType);
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

        $doctors = $query->get()->map(function ($doctor): array {
            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialty' => $doctor->specialty_name,
                'telephone' => $doctor->telephone,
                'email' => $doctor->email,
                'address' => $doctor->address,
                'doctor_type' => $doctor->doctor_type,
                'dental_split_mode' => null,
                'dental_split_value' => null,
                'availability_date' => $doctor->availability_date,
                'available_seats' => $doctor->available_seats,
            ];
        })->values();

        if ($doctors->isEmpty() && $request->routeIs('public.doctors.by-date')) {
            return response()->json([
                'message' => 'No doctors found for the selected date.',
            ]);
        }

        return response()->json([
            'data' => $doctors,
        ]);
    }

    public function billingConfig(Doctor $doctor): JsonResponse
    {
        $doctor->load('channellingFee:doctor_id,fee');

        $channelingService = Service::query()->where('key', ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value)->first();
        $dentalRegistrationService = Service::query()->where('key', ServiceKey::DENTAL_REGISTRATION->value)->first();
        $dentalServices = Service::query()
            ->where('key', 'like', 'dental-%')
            ->orderBy('name')
            ->get();

        return response()->json([
            'doctor_id' => $doctor->id,
            'doctor_type' => $doctor->doctor_type,
            'channeling' => [
                'consultation_referred_amount' => (float) ($doctor->channellingFee?->fee ?? $channelingService?->bill_price ?? 0),
                'booking_in_house_amount' => (float) ($channelingService?->system_price ?? 0),
            ],
            'dental' => [
                'registration_in_house_amount' => (float) ($dentalRegistrationService?->system_price ?? 0),
                'services' => $dentalServices->map(static function (Service $service): array {
                    return [
                        'service_id' => $service->id,
                        'name' => $service->name,
                        'system_price' => (float) $service->system_price,
                        'bill_price' => (float) $service->bill_price,
                    ];
                })->values()->all(),
            ],
        ]);
    }
}
