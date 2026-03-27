<?php

namespace App\Http\Controllers\PublicApi;

use App\Enums\AppointmentType;
use App\Enums\BillStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Controllers\Traits\BillTrait;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\DoctorAvailabilityTrait;
use App\Http\Controllers\Traits\OTPManager;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Controllers\Traits\SystemPriceCalculator;
use App\Http\Requests\PublicApi\StorePublicBookingRequest;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicBookingController extends Controller
{
    use BillItemsTrait;
    use BillTrait;
    use DailyPatientQueueTrait;
    use DoctorAvailabilityTrait;
    use OTPManager;
    use ServiceType;
    use SystemPriceCalculator;

    public function makeAppointment(StorePublicBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $this->checkPhoneHasVerified($data['phone']);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 422);
        }

        try {
            $this->adjustDoctorSeats($data['doctor_id'], $data['date']);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 422);
        }

        $patientId = $this->getOrCreatePatient(
            $data['name'],
            $data['phone'],
            $data['age'],
            $data['email'] ?? null,
            $data['user_id'] ?? null,
        );

        try {
            $this->hasPatientHasBook($data['date'], $patientId, $data['doctor_id']);
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
            'appointment_type' => $service->name,
            'date' => $data['date'],
            'status' => BillStatus::BOOKED,
        ]);

        $this->insertBillItems($service->id, $systemAmount, $billAmount, $bill->id);

        $bookingNumber = $this->createDailyPatientQueue($bill->id, $data['doctor_id'], $data['date']);

        [$doctorName, $doctorSpecialty] = $this->getDoctorDetails($data['doctor_id'], $data['doctor_type']);

        return response()->json([
            'doctor_name' => $doctorName,
            'doctor_specialty' => $doctorSpecialty,
            'booking_number' => $bookingNumber,
            'date' => $bill->date,
            'reference' => $bill->uuid,
            'bill_registration_number' => $bill->bill_registration_number,
            'booking_registration_number' => $bill->booking_registration_number,
            'generated_at' => $bill->created_at,
            'bill_id' => $bill->id,
        ]);
    }

    private function getOrCreatePatient(
        string $name,
        string $phone,
        int|float|string $age,
        ?string $email,
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
            ['age' => $age, 'email' => $email],
        );

        return $patient->id;
    }

    private function getDoctorDetails(int $doctorId, string $type): array
    {
        if ($type === AppointmentType::SPECIALIST->value) {
            $doctor = Doctor::query()->with('specialty:id,name')->findOrFail($doctorId);

            return [$doctor->name, $doctor->specialty->name];
        }

        $doctor = Doctor::query()->findOrFail($doctorId);

        return [$doctor->name, 'Dental Surgical Doctor'];
    }
}
