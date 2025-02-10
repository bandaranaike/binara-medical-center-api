<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\PrintingDataProcess;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Requests\Website\StoreBookingRequest;
use App\Models\Bill;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use BillItemsTrait;
    use DailyPatientQueueTrait;
    use PrintingDataProcess;
    use ServiceType;

    public function convertToBill(Request $request): JsonResponse
    {
        $bill = Bill::where('id', $request->get('bill_id'))
            ->with('patient:id,name')
            ->with('doctor:id,name')
            ->first();
        $billItems = $this->getBillItemsFroPrint($bill->id);

        $bill->status = Bill::STATUS_DOCTOR;
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

    public function getDoctorsList(Request $request): JsonResponse
    {

        $doctorTypes = [Doctor::DOCTOR_TYPE_SPECIALIST, Doctor::DOCTOR_TYPE_DENTAL];

        $validated = $request->validate([
            'doctor_type' => 'required|in:' . implode(',', $doctorTypes),
        ]);

        $doctors = Doctor::where('doctor_type', $validated['doctor_type'])
            ->with('specialty:id,name')
            ->select('id', 'name', 'specialty_id')
            ->get();

        return new JsonResponse($doctors);
    }


    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        // service_type:in(channeling|opd|dental)
        $service = $this->getService($request->input('service_type'));

        $bill = Bill::firstOrCreate(["id" => $request->get('bill_id')], [...$data, 'status' => Bill::STATUS_BOOKED]);

        $this->insertBillItems($service->id, $data['bill_amount'], $data['system_amount'], $bill->id);

        $booking_number = $this->createDailyPatientQueue($bill->id, $data['doctor_id']);


        return new JsonResponse(compact(
            "doctor_name",
            "booking_number",
            "date",
            "time",
            "reference",
            "generated_at",
            "bill_id"
        ));
    }

    public function getOrCreatePatient(Request $request): JsonResponse
    {

    }
}
