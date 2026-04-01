<?php

namespace App\Http\Controllers\PublicApi;

use App\Enums\AppointmentType;
use App\Enums\BillStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BillTrait;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\DoctorAvailabilityTrait;
use App\Http\Controllers\Traits\OTPManager;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Controllers\Traits\SystemPriceCalculator;
use App\Http\Requests\PublicApi\ListPublicBookingsRequest;
use App\Http\Requests\PublicApi\ProceedPublicBookingPaymentRequest;
use App\Http\Requests\PublicApi\StorePublicBookingRequest;
use App\Http\Requests\PublicApi\UpdatePublicBookingRequest;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Services\PublicBillingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicBookingController extends Controller
{
    use BillTrait;
    use DailyPatientQueueTrait;
    use DoctorAvailabilityTrait;
    use OTPManager;
    use ServiceType;
    use SystemPriceCalculator;

    public function __construct(private readonly PublicBillingService $publicBillingService) {}

    public function index(ListPublicBookingsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 20;

        $bookings = Bill::query()
            ->where('status', BillStatus::BOOKED->value)
            ->whereDate('date', $validated['date'])
            ->with([
                'patient:id,name,telephone,email,registration_no,age,gender,address,birthday',
                'doctor:id,name,specialty_id,doctor_type',
                'doctor.specialty:id,name',
                'dailyPatientQueue:id,bill_id,queue_number,queue_date',
                'billItems:id,bill_id,service_id,service_name,service_key,doctor_id,bill_amount,system_amount,referred_amount,category,is_ad_hoc',
                'billItems.service:id,name,key',
            ])
            ->when(
                $validated['doctor_id'] ?? null,
                fn ($query, $doctorId) => $query->where('doctor_id', $doctorId),
            )
            ->when(
                $validated['search'] ?? null,
                function ($query, $search): void {
                    $query->where(function ($builder) use ($search): void {
                        $builder->where('uuid', 'like', '%'.$search.'%')
                            ->orWhereHas('patient', function ($patientQuery) use ($search): void {
                                $patientQuery->where('name', 'like', '%'.$search.'%')
                                    ->orWhere('telephone', 'like', '%'.$search.'%')
                                    ->orWhere('registration_no', 'like', '%'.$search.'%');
                            })
                            ->orWhereHas('doctor', function ($doctorQuery) use ($search): void {
                                $doctorQuery->where('name', 'like', '%'.$search.'%');
                            });
                    });
                },
            )
            ->orderBy('id')
            ->paginate($perPage);

        //        Log::info('Public bookings', ['bookings' => $bookings]);

        return response()->json([
            'data' => collect($bookings->items())
                ->map(fn (Bill $booking): array => $this->serializeBooking($booking))
                ->all(),
            'meta' => $this->paginationMeta($bookings),
        ]);
    }

    public function show(Bill $booking): JsonResponse
    {
        if (! $this->isBooked($booking)) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        $booking->load([
            'patient:id,name,telephone,email,registration_no,age,gender,address,birthday',
            'doctor:id,name,specialty_id,doctor_type',
            'doctor.specialty:id,name',
            'dailyPatientQueue:id,bill_id,queue_number,queue_date',
            'billItems:id,bill_id,service_id,service_name,service_key,doctor_id,bill_amount,system_amount,referred_amount,category,is_ad_hoc',
            'billItems.service:id,name,key',
        ]);

        return response()->json($this->serializeBooking($booking));
    }

    public function makeAppointment(StorePublicBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $patientId = $this->getOrCreatePatient(
            $data['name'],
            $data['phone'],
            $data['age'],
            $data['email'] ?? null,
            $data['registration_no'] ?? null,
            $data['address'] ?? null,
            $data['user_id'] ?? null,
        );

        try {
            $this->hasPatientHasBook($data['date'], $patientId, $data['doctor_id']);
            $this->adjustDoctorSeats($data['doctor_id'], $data['date']);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 422);
        }

        $service = $this->getService($data['doctor_type']);
        [$billAmount, $systemAmount] = $this->getBillPriceAndSystemPrice($service);

        $bill = Bill::create([
            'system_amount' => $systemAmount,
            'bill_amount' => $billAmount,
            'patient_id' => $patientId,
            'doctor_id' => $data['doctor_id'],
            'appointment_type' => $service?->name ?? $data['doctor_type'],
            'date' => $data['date'],
            'status' => BillStatus::BOOKED,
        ]);

        if ($service !== null) {
            $this->publicBillingService->createDefaultBillItem(
                $bill,
                $service,
                (float) $billAmount,
                (float) $systemAmount,
                $data['doctor_id'],
                $data['doctor_type'],
            );
        }

        $bookingNumber = $this->createDailyPatientQueue($bill->id, $data['doctor_id'], $data['date']);

        [$doctorName, $doctorSpecialty] = $this->getDoctorDetails($data['doctor_id'], $data['doctor_type']);

        return response()->json([
            'doctor_name' => $doctorName,
            'doctor_specialty' => $doctorSpecialty,
            'booking_number' => $bookingNumber,
            'date' => $bill->date,
            'reference' => $bill->uuid,
            'generated_at' => $bill->created_at,
            'bill_id' => $bill->id,
        ]);
    }

    public function update(UpdatePublicBookingRequest $request, Bill $booking): JsonResponse
    {
        if (! $this->isBooked($booking)) {
            return response()->json([
                'message' => 'Only bookings in booked status can be updated.',
            ], 409);
        }

        $validated = $request->validated();
        $oldDoctorId = $booking->doctor_id;
        $oldDate = $booking->date;
        $hasSlotChanged = (int) $oldDoctorId !== (int) $validated['doctor_id'] || $oldDate !== $validated['date'];
        $service = $this->getService($validated['service_type']);

        try {
            DB::transaction(function () use ($booking, $validated, $oldDoctorId, $oldDate, $hasSlotChanged, $service): void {
                if ($hasSlotChanged) {
                    $this->restoreDoctorSeats($oldDoctorId, $oldDate);
                    $this->adjustDoctorSeats($validated['doctor_id'], $validated['date']);
                }

                $booking->patient()->update([
                    'name' => $validated['patient']['name'],
                    'telephone' => $this->normalizePhone($validated['patient']['telephone']),
                    'email' => $validated['patient']['email'] ?? null,
                    'registration_no' => $validated['patient']['registration_no'] ?? null,
                    'age' => $validated['patient']['age'],
                    'gender' => $validated['patient']['gender'] ?? null,
                    'address' => $validated['patient']['address'] ?? null,
                    'birthday' => $validated['patient']['birthday'] ?? null,
                ]);

                $booking->update([
                    'doctor_id' => $validated['doctor_id'],
                    'date' => $validated['date'],
                    'shift' => $validated['shift'],
                    'payment_type' => $validated['payment_type'],
                    'bill_amount' => $validated['bill_amount'],
                    'system_amount' => $validated['system_amount'],
                    'appointment_type' => $service?->name ?? $validated['service_type'],
                ]);

                if (! empty($validated['items'])) {
                    $this->publicBillingService->replaceBillItems(
                        $booking,
                        $validated['items'],
                        $validated['doctor_id'],
                        $validated['service_type'],
                    );
                } elseif ($service !== null) {
                    $booking->billItems()->delete();
                    $this->publicBillingService->createDefaultBillItem(
                        $booking,
                        $service,
                        (float) $validated['bill_amount'],
                        (float) $validated['system_amount'],
                        $validated['doctor_id'],
                        $validated['service_type'],
                    );
                }

                if ($hasSlotChanged) {
                    $booking->dailyPatientQueue()?->delete();
                    $this->createDailyPatientQueue($booking->id, $validated['doctor_id'], $validated['date']);
                }
            });
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'doctor_id' => [$exception->getMessage()],
                ],
            ], 422);
        }

        $booking->load('dailyPatientQueue:id,bill_id,queue_number,queue_date');

        return response()->json([
            'message' => 'Booking updated successfully.',
            'booking' => [
                'id' => $booking->id,
                'bill_id' => $booking->id,
                'reference' => $booking->uuid,
                'booking_number' => $booking->dailyPatientQueue?->queue_number,
                'date' => $booking->date,
                'status' => $booking->status,
            ],
        ]);
    }

    public function destroy(Bill $booking): JsonResponse
    {
        if (! $this->isBooked($booking)) {
            return response()->json([
                'message' => 'Only bookings in booked status can be deleted.',
            ], 409);
        }

        DB::transaction(function () use ($booking): void {
            $this->restoreDoctorSeats($booking->doctor_id, $booking->date);
            $booking->dailyPatientQueue()?->delete();
            $booking->billItems()->delete();
            $booking->delete();
        });

        return response()->json([
            'message' => 'Booking deleted successfully.',
            'deleted_id' => $booking->id,
        ]);
    }

    public function proceedToPayment(ProceedPublicBookingPaymentRequest $request, Bill $booking): JsonResponse
    {
        if (! $this->isBooked($booking)) {
            return response()->json([
                'message' => 'This booking has already been processed.',
            ], 409);
        }

        $validated = $request->validated();

        $booking->update([
            'payment_type' => $validated['payment_type'],
            'shift' => $validated['shift'],
            'bill_amount' => $validated['bill_amount'],
            'system_amount' => $validated['system_amount'],
            'status' => BillStatus::DOCTOR,
        ]);

        if (! empty($validated['items'])) {
            $this->publicBillingService->replaceBillItems(
                $booking,
                $validated['items'],
                $booking->doctor_id,
                $booking->doctor?->doctor_type,
            );
        } elseif ($booking->billItems()->exists()) {
            $booking->billItems()->update([
                'bill_amount' => $validated['bill_amount'],
                'system_amount' => $validated['system_amount'],
                'referred_amount' => round((float) $validated['bill_amount'] - (float) $validated['system_amount'], 2),
            ]);
        }

        $booking->load('billItems.service');

        return response()->json([
            'message' => 'Booking moved to payment successfully.',
            'bill' => [
                'id' => $booking->id,
                'reference' => $booking->uuid,
                'status' => $booking->status,
                'payment_type' => $booking->payment_type,
                'bill_amount' => (float) $booking->bill_amount,
                'system_amount' => (float) $booking->system_amount,
                'date' => $booking->date,
                'items' => $booking->billItems
                    ->map(fn ($billItem): array => $this->publicBillingService->serializeBillItem($billItem))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    private function getOrCreatePatient(
        string $name,
        string $phone,
        int|float|string $age,
        ?string $email,
        ?string $registrationNo,
        ?string $address,
        ?string $userUuid,
    ): int {
        if ($userUuid !== null) {
            $user = User::query()->where('uuid', $userUuid)->firstOrFail();
        } else {
            $patientRoleId = Role::query()
                ->where('key', UserRole::PATIENT->value)
                ->value('id');

            $user = User::query()->firstOrCreate(
                ['phone' => $phone],
                [
                    'email' => $email,
                    'name' => $name,
                    'role_id' => $patientRoleId,
                    'phone_verified_at' => now(),
                    'password' => Hash::make(Str::random(8)),
                ],
            );
        }

        $patient = Patient::query()->firstOrCreate(
            ['name' => $name, 'telephone' => $phone, 'user_id' => $user->id],
            [
                'age' => $age,
                'email' => $email,
                'registration_no' => $registrationNo,
                'address' => $address,
            ],
        );

        if (
            $registrationNo !== null
            && Patient::query()
                ->where('registration_no', $registrationNo)
                ->whereKeyNot($patient->id)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'registration_no' => ['The registration no has already been taken.'],
            ]);
        }

        $patientUpdates = array_filter([
            'email' => $email,
            'registration_no' => $registrationNo,
            'address' => $address,
        ], static fn (mixed $value): bool => $value !== null);

        if ($patientUpdates !== []) {
            $patient->fill($patientUpdates);

            if ($patient->isDirty()) {
                $patient->save();
            }
        }

        return $patient->id;
    }

    private function getDoctorDetails(int $doctorId, string $type): array
    {
        if ($type === AppointmentType::SPECIALIST->value) {
            $doctor = Doctor::query()->with('specialty:id,name')->findOrFail($doctorId);

            return [$doctor->name, $doctor->specialty?->name];
        }

        $doctor = Doctor::query()->findOrFail($doctorId);

        return [$doctor->name, 'Dental Surgical Doctor'];
    }

    private function isBooked(Bill $booking): bool
    {
        return $booking->status === BillStatus::BOOKED->value;
    }

    private function normalizePhone(string $telephone): string
    {
        return Str::replaceMatches('/^0/', '+94', trim($telephone));
    }

    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    private function serializeBooking(Bill $booking): array
    {
        return [
            'id' => $booking->id,
            'bill_id' => $booking->id,
            'reference' => $booking->uuid,
            'booking_number' => $booking->dailyPatientQueue?->queue_number,
            'date' => $booking->date,
            'status' => $booking->status,
            'patient' => [
                'id' => $booking->patient?->id,
                'name' => $booking->patient?->name,
                'telephone' => $booking->patient?->telephone,
                'email' => $booking->patient?->email,
                'age' => $booking->patient?->age,
                'gender' => $booking->patient?->gender,
                'address' => $booking->patient?->address,
                'birthday' => $booking->patient?->birthday,
                'registration_no' => $booking->patient?->registration_no,
            ],
            'doctor' => [
                'id' => $booking->doctor?->id,
                'name' => $booking->doctor?->name,
                'specialty' => $booking->doctor?->specialty?->name,
                'doctor_type' => $booking->doctor?->doctor_type,
            ],
            'doctor_name' => $booking->doctor?->name,
            'doctor_specialty' => $booking->doctor?->specialty?->name,
            'payment_type' => $booking->payment_type,
            'shift' => $booking->shift,
            'service_type' => $booking->doctor?->doctor_type,
            'bill_amount' => (float) $booking->bill_amount,
            'system_amount' => (float) $booking->system_amount,
            'items' => $booking->billItems
                ->map(fn ($item): array => $this->publicBillingService->serializeBillItem($item))
                ->values()
                ->all(),
            'created_at' => $booking->created_at?->toISOString(),
        ];
    }
}
