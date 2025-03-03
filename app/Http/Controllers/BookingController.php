<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentType;
use App\Enums\BillStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Controllers\Traits\BillTrait;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\DoctorAvailabilityTrait;
use App\Http\Controllers\Traits\OTPManager;
use App\Http\Controllers\Traits\PrintingDataProcess;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Requests\Website\StoreBookingRequest;
use App\Http\Resources\PatientAppointmentHistory;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    use BillItemsTrait;
    use BillTrait;
    use DailyPatientQueueTrait;
    use DoctorAvailabilityTrait;
    use OTPManager;
    use PrintingDataProcess;
    use ServiceType;

    public function makeAppointment(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $this->checkPhoneHasVerified($data['phone']);
        } catch (Exception $exception) {
            return new JsonResponse($exception->getMessage(), 422);
        }

        try {
            $this->adjustDoctorSeats($data['doctor_id'], $data['date']);
        } catch (Exception $exception) {
            return new JsonResponse($exception->getMessage(), 422);
        }

        $patientId = $this->getOrCreatePatient($data['name'], $data['phone'], $data['age'], $data['email'], $request->input('user_id'));

        try {
            $this->hasPatientHasBook($data['date'], $patientId, $data['doctor_id']);
        } catch (Exception $exception) {
            return new JsonResponse($exception->getMessage(), 422);
        }

        $service = $this->getService($request->input('doctor_type'));
        [$billAmount, $systemAmount] = $this->getBillPriceAndSystemPrice($service);

        $bill = Bill::create([
            'system_amount' => $systemAmount,
            "bill_amount" => $billAmount,
            "patient_id" => $patientId,
            "doctor_id" => $data['doctor_id'],
            "appointment_type" => $service->name,
            "date" => $data['date'],
            'status' => BillStatus::BOOKED
        ]);

        $this->insertBillItems($service->id, $systemAmount, $billAmount, $bill->id);

        $bookingNumber = $this->createDailyPatientQueue($bill->id, $data['doctor_id']);

        [$doctorName, $doctorSpecialty] = $this->getDoctorDetails($data['doctor_id'], $data['doctor_type']);

        return new JsonResponse(array(
            "doctor_name" => $doctorName,
            "doctor_specialty" => $doctorSpecialty,
            "booking_number" => $bookingNumber,
            "date" => $bill->date,
            "reference" => $bill->uuid,
            "generated_at" => $bill->created_at,
            "bill_id" => $bill->id
        ));
    }

    public function getOrCreatePatient($name, $phone, $age, $email, $user_uuid): int
    {
        if ($user_uuid) {
            $user = User::where('uuid', $user_uuid)->first();
        } else {
            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'email' => $email,
                    'name' => $name,
                    'role_id' => Role::where("key", UserRole::PATIENT->value)->first()->id,
                    'phone_verified_at' => now(),
                    'password' => Hash::make(Str::random(8)),
                ],
            );
        }

        $patient = Patient::firstOrCreate(
            ['name' => $name, 'telephone' => $phone, 'user_id' => $user->id],
            ['age' => $age, 'email' => $email]
        );
        return $patient->id;
    }

    private function getDoctorDetails($doctorId, $type): array
    {
        if ($type == AppointmentType::SPECIALIST) {
            $doctor = Doctor::with('specialty:id,name')->find($doctorId);
            return [$doctor->name, $doctor->specialty->name];
        } else {
            $doctor = Doctor::find($doctorId);
            return [$doctor->name, "Dental Surgical Doctor"];
        }
    }

    public function getPatientsHistoryForWeb(Request $request): JsonResponse
    {
        $patientBillHistories = Bill::whereIn('patient_id', $request->get('ensure_middleware_patient_ids'))
            ->with('doctor.specialty:id,name')
            ->with('patient:id,name')
            ->with('doctor:id,name,specialty_id')
            ->select(['id', 'patient_id', 'doctor_id', 'payment_status', 'appointment_type', 'status', 'date'])
            ->get();

        return new JsonResponse(PatientAppointmentHistory::collection($patientBillHistories));
    }
}
