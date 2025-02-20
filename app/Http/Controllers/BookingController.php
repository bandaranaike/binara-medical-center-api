<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentType;
use App\Enums\BillStatus;
use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Controllers\Traits\BillTrait;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\DoctorAvailabilityTrait;
use App\Http\Controllers\Traits\PrintingDataProcess;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Requests\Website\StoreBookingRequest;
use App\Http\Resources\PatientAppointmentHistory;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    use BillItemsTrait;
    use BillTrait;
    use DailyPatientQueueTrait;
    use DoctorAvailabilityTrait;
    use PrintingDataProcess;
    use ServiceType;

    public function convertToBill(Request $request): JsonResponse
    {
        $bill = Bill::where('id', $request->get('bill_id'))
            ->with('patient:id,name')
            ->with('doctor:id,name')
            ->first();
        $billItems = $this->getBillItemsFroPrint($bill->id);

        $bill->status = BillStatus::DOCTOR;
        $bill->save();

        return new JsonResponse([
            "bill_reference" => '',
            "payment_type" => $bill->payment_type,
            'patient_name' => $bill->patient->name,
            'doctor_name' => $bill->doctor?->name,
            "bill_items" => $billItems,
            'total' => $bill->bill_amount + $bill->system_amount
        ]);
    }

    public function makeAppointment(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

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
        $user = User::where('uuid', $user_uuid)->first();
        $patient = Patient::firstOrCreate(['name' => $name, 'telephone' => $phone, 'user_id' => $user?->id], ['age' => $age, 'email' => $email]);
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
