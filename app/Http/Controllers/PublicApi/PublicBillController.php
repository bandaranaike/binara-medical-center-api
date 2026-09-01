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
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicBillController extends Controller
{
    use DailyPatientQueueTrait;
    use ServiceType;

    public function __construct(private readonly PublicBillingService $publicBillingService) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $date = $validated['date'] ?? now()->toDateString();

        $bills = Bill::withTrashed()
            ->whereDate('date', $date)
            ->where('payment_status', BillPaymentStatus::PAID->value)
            ->with([
                'patient:id,name,telephone,email,registration_no,age,gender,address,birthday',
                'doctor:id,name,specialty_id,doctor_type',
                'doctor.specialty:id,name',
                'billItems:id,bill_id,service_id,service_name,service_key,doctor_id,referred_amount,system_amount,category,is_ad_hoc',
                'billItems.service:id,name,key',
            ])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'date' => $date,
            'data' => $bills
                ->map(fn (Bill $bill): array => $this->serializeBill($bill))
                ->values()
                ->all(),
        ]);
    }

    public function store(StorePublicBillRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $service = $this->getService($payload['service_type']);
        $status = $payload['is_booking'] ? BillStatus::BOOKED : BillStatus::DOCTOR;
        $appointmentType = $service?->name
            ?? ($payload['items'][0]['service_name'] ?? Str::headline($payload['service_type']));

        $bill = Bill::create([
            'system_amount' => $payload['system_amount'],
            'referred_amount' => $payload['referred_amount'],
            'patient_id' => $payload['patient_id'],
            'doctor_id' => $payload['doctor_id'],
            'date' => $payload['is_booking'] ? $payload['date'] : now()->toDateString(),
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
                (float) $payload['referred_amount'],
                (float) $payload['system_amount'],
                $payload['doctor_id'] ?? null,
                $payload['service_type'],
            );
        }

        if (($payload['doctor_id'] ?? null) !== null) {
            $this->createDailyPatientQueue($bill->id, $payload['doctor_id'], $bill->date);
        }

        event(new NewBillCreated($bill));

        $bill->load('billItems.service');

        return response()->json([
            'id' => $bill->id,
            'uuid' => $bill->uuid,
            'reference' => $bill->uuid,
            'patient_id' => $bill->patient_id,
            'doctor_id' => $bill->doctor_id,
            'referred_amount' => (float) $bill->referred_amount,
            'system_amount' => (float) $bill->system_amount,
            'total_amount' => round((float) $bill->referred_amount + (float) $bill->system_amount, 2),
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

    public function destroy(Bill $bill): JsonResponse
    {
        $bill->delete();

        return response()->json([
            'message' => 'Bill deleted successfully.',
            'deleted_id' => $bill->id,
            'deleted_at' => $bill->deleted_at?->toISOString(),
        ]);
    }

    private function serializeBill(Bill $bill): array
    {
        $firstItem = $bill->billItems->first();

        return [
            'id' => $bill->id,
            'uuid' => $bill->uuid,
            'reference' => $bill->uuid,
            'date' => $bill->date,
            'shift' => $bill->shift,
            'status' => $bill->status,
            'payment_type' => $bill->payment_type,
            'payment_status' => $bill->payment_status,
            'appointment_type' => $bill->appointment_type,
            'service_type' => $firstItem?->category ?? $bill->doctor?->doctor_type,
            'referred_amount' => (float) $bill->referred_amount,
            'system_amount' => (float) $bill->system_amount,
            'total_amount' => round((float) $bill->referred_amount + (float) $bill->system_amount, 2),
            'deleted_at' => $bill->deleted_at?->toISOString(),
            'patient' => $bill->patient === null ? null : [
                'id' => $bill->patient->id,
                'name' => $bill->patient->name,
                'telephone' => $bill->patient->telephone,
                'registration_no' => $bill->patient->registration_no,
            ],
            'doctor' => $bill->doctor === null ? null : [
                'id' => $bill->doctor->id,
                'name' => $bill->doctor->name,
                'specialty' => $bill->doctor->specialty?->name,
                'doctor_type' => $bill->doctor->doctor_type,
            ],
            'items' => $bill->billItems
                ->map(fn ($billItem): array => $this->publicBillingService->serializeBillItem($billItem))
                ->values()
                ->all(),
        ];
    }
}
