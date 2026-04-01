<?php

namespace App\Http\Controllers\PublicApi;

use App\Enums\BillPaymentStatus;
use App\Enums\BillStatus;
use App\Events\NewBillCreated;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Requests\PublicApi\StorePublicBillRequest;
use App\Models\Bill;
use App\Services\PublicBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PublicBillController extends Controller
{
    use DailyPatientQueueTrait;
    use ServiceType;

    public function __construct(private readonly PublicBillingService $publicBillingService) {}

    public function store(StorePublicBillRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $service = $this->getService($payload['service_type']);
        $status = $payload['is_booking'] ? BillStatus::BOOKED : BillStatus::DOCTOR;
        $appointmentType = $service?->name
            ?? ($payload['items'][0]['service_name'] ?? Str::headline($payload['service_type']));

        $bill = Bill::create([
            'system_amount' => $payload['system_amount'],
            'bill_amount' => $payload['bill_amount'],
            'patient_id' => $payload['patient_id'],
            'doctor_id' => $payload['doctor_id'],
            'date' => $payload['date'],
            'shift' => $payload['shift'],
            'payment_type' => $payload['payment_type'],
            'payment_status' => BillPaymentStatus::PAID,
            'appointment_type' => $appointmentType,
            'status' => $status,
        ]);

        if (! empty($payload['items'])) {
            $this->publicBillingService->replaceBillItems(
                $bill,
                $payload['items'],
                $payload['doctor_id'] ?? null,
                $payload['service_type'],
            );
        } elseif ($service !== null) {
            $this->publicBillingService->createDefaultBillItem(
                $bill,
                $service,
                (float) $payload['bill_amount'],
                (float) $payload['system_amount'],
                $payload['doctor_id'] ?? null,
                $payload['service_type'],
            );
        }

        if (($payload['doctor_id'] ?? null) !== null) {
            $this->createDailyPatientQueue($bill->id, $payload['doctor_id'], $payload['date']);
        }

        event(new NewBillCreated($bill));

        $bill->load('billItems.service');

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
            'items' => $bill->billItems
                ->map(fn ($billItem): array => $this->publicBillingService->serializeBillItem($billItem))
                ->values()
                ->all(),
        ], 201);
    }
}
