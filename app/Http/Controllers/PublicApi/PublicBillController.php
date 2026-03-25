<?php

namespace App\Http\Controllers\PublicApi;

use App\Enums\BillPaymentStatus;
use App\Enums\BillStatus;
use App\Events\NewBillCreated;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Requests\PublicApi\StorePublicBillRequest;
use App\Models\Bill;
use Illuminate\Http\JsonResponse;

class PublicBillController extends Controller
{
    use BillItemsTrait;
    use DailyPatientQueueTrait;
    use ServiceType;

    public function store(StorePublicBillRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $service = $this->getService($payload['service_type']);
        $status = $payload['is_booking'] ? BillStatus::BOOKED : BillStatus::DOCTOR;

        $bill = Bill::create([
            'system_amount' => $payload['system_amount'],
            'bill_amount' => $payload['bill_amount'],
            'patient_id' => $payload['patient_id'],
            'doctor_id' => $payload['doctor_id'],
            'date' => $payload['date'],
            'shift' => $payload['shift'],
            'payment_type' => $payload['payment_type'],
            'payment_status' => BillPaymentStatus::PENDING,
            'appointment_type' => $service?->name ?? $payload['service_type'],
            'status' => $status,
        ]);

        if ($service !== null) {
            $this->insertBillItems($service->id, $payload['bill_amount'], $payload['system_amount'], $bill->id);
        }

        $this->createDailyPatientQueue($bill->id, $payload['doctor_id'], $payload['date']);

        event(new NewBillCreated($bill));

        return response()->json([
            'id' => $bill->id,
            'uuid' => $bill->uuid,
            'reference' => $bill->uuid,
            'patient_id' => $bill->patient_id,
            'doctor_id' => $bill->doctor_id,
            'bill_amount' => (float) $bill->bill_amount,
            'system_amount' => (float) $bill->system_amount,
            'payment_type' => $bill->payment_type,
            'payment_status' => $bill->payment_status,
            'status' => $bill->status,
            'service_type' => $payload['service_type'],
            'shift' => $bill->shift,
            'date' => $bill->date,
        ], 201);
    }
}
