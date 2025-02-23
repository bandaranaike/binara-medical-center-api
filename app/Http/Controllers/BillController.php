<?php

namespace App\Http\Controllers;

use App\Enums\BillPaymentStatus;
use App\Enums\BillStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Controllers\Traits\DailyPatientQueueTrait;
use App\Http\Controllers\Traits\PrintingDataProcess;
use App\Http\Controllers\Traits\ServiceType;
use App\Http\Controllers\Traits\SystemPriceCalculator;
use App\Http\Requests\ChangeBillStatusRequest;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillReceptionResourceCollection;
use App\Http\Resources\BillResource;
use App\Http\Resources\BookingListResource;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Doctor;
use App\Models\Service;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;


class BillController extends Controller
{
    use BillItemsTrait;
    use DailyPatientQueueTrait;
    use PrintingDataProcess;
    use ServiceType;
    use SystemPriceCalculator;

    const OLD_BOOKING_KEYWORD = 'old';

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $bills = Bill::all();
        return BillResource::collection($bills);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillRequest $request): JsonResponse
    {
        $status = $request->input('is_booking') ? BillStatus::BOOKED : BillStatus::DOCTOR;
        $data = $request->validated();

        // service_type:in(channeling|opd|dental)
        $service = $this->getService($request->input('service_type'));

        $bill = Bill::firstOrCreate(
            ["id" => $request->get('bill_id')],
            [...$data, 'status' => $status, 'appointment_type' => $service->name]
        );

        $this->insertBillItems($service->id, $data['bill_amount'], $data['system_amount'], $bill->id);

        $queueNumber = $this->createDailyPatientQueue($bill->id, $data['doctor_id']);

        return new JsonResponse([
            ...$this->billPrintingResponse($bill),
            "queue_id" => $queueNumber,
        ], 201);
    }

    private function billPrintingResponse($bill): array
    {
        return [
            "bill_reference" => '',
            "payment_type" => $bill->payment_type,
            "bill_id" => $bill->id,
            "bill_items" => $this->getBillItemsFroPrint($bill->id),
            'patient_name' => $bill->patient->name,
            'doctor_name' => $bill->doctor?->name,
            'total' => $bill->bill_amount + $bill->system_amount
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill): BillResource
    {
        return new BillResource($bill->load('billItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill): BillResource
    {
        $bill->update($request->only('status'));

        return new BillResource($bill->load('billItems'));
    }

    /**
     * Get all pending bills.
     *
     * @return JsonResponse
     */
    public function getPendingBillsForDoctor(): JsonResponse
    {
        $doctorId = request('doctor_id');
        $pendingBills = Bill::where('status', BillStatus::DOCTOR)
            ->with(['patient.allergies:id,name', 'patient.diseases:id,name'])
            ->with('patient', function ($query) use ($doctorId) {
                $query->select(['id', 'name', 'age', 'gender'])
                    ->with('patientHistories', function ($query) use ($doctorId) {
                        $query->where('doctor_id', $doctorId)
                            ->select(['id', 'note', 'patient_id', 'doctor_id', 'created_at']);
                    })
                    ->orderBy('created_at', 'desc');
            })
            ->join('daily_patient_queues', 'bills.id', '=', 'daily_patient_queues.bill_id', 'left')
            ->select(['bills.id', 'patient_id', 'uuid', 'bills.doctor_id', 'daily_patient_queues.queue_number'])
            ->where('bills.doctor_id', '=', $doctorId)
            ->orderBy('daily_patient_queues.order_number')
            ->get();

        return new JsonResponse($pendingBills);
    }

    /**
     * Get all pending bills.
     *
     * @return JsonResponse
     */
    public function getPendingBillsForPharmacy(): JsonResponse
    {
        $pendingBillsQuery = Bill::where('status', BillStatus::PHARMACY)
            ->with([
                'patient:id,name,age,gender',
                'doctor:id,name',
            ])
            ->with(['billItems' => function ($query) {
                $query->with('service:id,name')
                    ->select('id', 'bill_id', 'service_id', 'system_amount', 'bill_amount'); // Load only necessary fields for bill items
            }])
            ->with('patientMedicines', function ($query) {
                $query->with('medicine:id,name', 'medicationFrequency:id,name')
                    ->select('id', 'bill_id', 'medicine_id', 'medication_frequency_id', 'duration');
            });

        if (Auth::user()->hasRole(UserRole::DOCTOR->value)) {
            $pendingBillsQuery->where('doctor_id', Doctor::where('user_id', Auth::id())->first('id')?->id);
        }

        $pendingBills = $pendingBillsQuery->get();

        return new JsonResponse($pendingBills);
    }

    /**
     * Get all pending bills.
     *
     * @return JsonResponse
     */
    public function getPendingBillsForReception(): JsonResponse
    {
        $pendingBills = Bill::where('status', "!=", BillStatus::DONE)
            ->with([
                'patient:id,name,age,gender',
                'doctor:id,name',
                'dailyPatientQueue:id,bill_id,queue_number,queue_date',
            ])
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderByDesc('id')
            ->get();

        return new JsonResponse(BillReceptionResourceCollection::collection($pendingBills));
    }

    /**
     * Finalize the bill by updating its status and bill amount.
     *
     * @param Request $request
     * @param int $billId
     * @return JsonResponse
     */
    public function sendBillToReception(Request $request, int $billId): JsonResponse
    {
        $validatedData = $request->validate([
            'status' => 'required|string|in:' . BillStatus::RECEPTION->value,
            'bill_amount' => 'required|numeric|min:0',
            'system_amount' => 'required|numeric|min:0',
        ]);

        try {
            // Find the bill by ID
            $bill = Bill::findOrFail($billId);

            // Update the bill's status and bill amount
            $bill->status = $validatedData['status'];
            $bill->bill_amount = $validatedData['bill_amount'];
            $bill->system_amount = $validatedData['system_amount'];
            $bill->save();

            return response()->json([
                'message' => 'Bill finalized successfully',
                'data' => $bill
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to finalize the bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus($billId, UpdateBillRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!$bill = Bill::with('patient:id,name')
            ->with('doctor:id,name')->find($billId)) {
            return new JsonResponse(['message' => 'Bill not found'], 404);
        }

        if ($validated['status'] == BillStatus::DONE->value) {
            $bill->payment_status = BillPaymentStatus::PAID;
        }

        if ($validated['status'] === BillStatus::PHARMACY) {
            $this->insertNewBillItemForMedicineIfNotExists($billId);
        }

        $bill->status = $validated['status'];
        $bill->save();

        if ($validated['status'] === BillStatus::DONE->value) {
            return new JsonResponse($this->billPrintingResponse($bill));
        }

        return new JsonResponse(['message' => 'Bill status updated successfully', 'bill' => $bill]);
    }

    private function insertNewBillItemForMedicineIfNotExists($billId): void
    {
        BillItem::firstOrCreate(['bill_id' => $billId, 'service_id' => Service::where('key', Service::MEDICINE_KEY)->first()->id]);
    }

    public function bookings($time = null): JsonResponse
    {
        $bookingsQuery = Bill::where('status', BillStatus::BOOKED)
            ->with(['doctor:id,name', 'patient:id,name', 'dailyPatientQueue:id,bill_id,queue_number,queue_date']);
        if ($time === self::OLD_BOOKING_KEYWORD) {
            $bookingsQuery->where('created_at', '<', now()->subDays());
        } else {
            $bookingsQuery->where('created_at', '>=', now()->subDays());
        }

        $bookings = $bookingsQuery->get();

        return new JsonResponse(BookingListResource::collection($bookings));
    }

    public function changeTempBillStatus(ChangeBillStatusRequest $request, $billId): JsonResponse
    {
        $validatedData = $request->validated();

        $status = $validatedData['is_booking'] ? BillStatus::BOOKED : ($validatedData['doctor_id'] ? BillStatus::DOCTOR : BillStatus::PHARMACY);

        $doctorId = $validatedData['doctor_id'] == 0 ? null : $validatedData['doctor_id'];

        Bill::where('id', $billId)
            ->update([
                'status' => $status,
                'doctor_id' => $doctorId,
                'patient_id' => $validatedData['patient_id'],
                'bill_amount' => $validatedData['bill_amount']
            ]);

        $queueNumber = $this->createDailyPatientQueue($billId, $doctorId);

        return response()->json(["bill_id" => $billId, "queue_id" => $queueNumber]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $bill = Bill::where('status', BillStatus::BOOKED)->where('id', $id)->first();
            if (!$bill) {
                return new JsonResponse(['message' => 'Bill not found'], 404);
            }
            $bill->dailyPatientQueue()->delete();
            $bill->billItems()->delete();
            $bill->delete();
            return new JsonResponse(['message' => 'Bill deleted successfully'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Error deleting bill', 'error' => $e->getMessage()], 500);
        }
    }

}
