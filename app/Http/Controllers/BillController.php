<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\SystemPriceCalculator;
use App\Http\Requests\ChangeBillStatusRequest;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillReceptionResourceCollection;
use App\Http\Resources\BillResource;
use App\Http\Resources\BookingListResource;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\DailyPatientQueue;
use App\Models\Service;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BillController extends Controller
{

    use SystemPriceCalculator;
    const OLD_BOOKING_KEYWORD = 'old';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bills = Bill::all();
        return BillResource::collection($bills);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillRequest $request): JsonResponse
    {
        $status = $request->input('is_booking') ? Bill::STATUS_BOOKED : Bill::STATUS_DOCTOR;
        $data = $request->validated();

        // service_type:in(channeling|opd|dental)
        $service = $this->getService($request->input('service_type'));

        $system_amount = $this->calculateSystemPrice($service, $data['bill_amount'], $data['system_amount']);

        $bill = Bill::create([...$data, 'status' => $status, 'system_amount' => $system_amount]);

        $this->insertBillItems($service->id, $data['bill_amount'], $data['system_amount'], $bill->id);

        $queueNumber = $this->createDailyPatientQueue($bill->id, $data['doctor_id']);

        $billItems = $this->getBillItemsFroPrint($bill->id);

        return new JsonResponse(["bill_id" => $bill->id, "queue_id" => $queueNumber, "bill_items" => $billItems, 'total' => $bill->bill_amount + $bill->system_amount], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        return new BillResource($bill->load('billItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        $bill->update($request->only('status'));

        return new BillResource($bill->load('billItems'));
    }

    public function getNextBillNumber(): JsonResponse
    {
        return new JsonResponse(Bill::latest()->first()->id + 1);
    }

    /**
     * Get all pending bills.
     *
     * @return JsonResponse
     */
    public function getPendingBillsForDoctor(): JsonResponse
    {
        $doctorId = Auth::id();
        $pendingBills = Bill::where('status', Bill::STATUS_DOCTOR)
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
            ->select(['bills.id', 'patient_id', 'bills.doctor_id', 'daily_patient_queues.queue_number'])
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
        $pendingBills = Bill::where('status', Bill::STATUS_PHARMACY)
            ->with([
                'patient:id,name,age,gender',
                'doctor:id,name',
            ])
            ->with(['billItems' => function ($query) {
                $query->with('service:id,name')
                    ->select('id', 'bill_id', 'service_id', 'bill_amount'); // Load only necessary fields for bill items
            }])
            ->with('patientMedicines', function ($query) {
                $query->with('medicine:id,name')
                    ->select('id', 'bill_id', 'medicine_id', 'dosage', 'type', 'duration');
            })
            ->get();

        return new JsonResponse($pendingBills);
    }

    /**
     * Get all pending bills.
     *
     * @return JsonResponse
     */
    public function getPendingBillsForReception(): JsonResponse
    {
        $pendingBills = Bill::where('status', Bill::STATUS_RECEPTION)
            ->with([
                'patient:id,name,age,gender',
                'doctor:id,name',
                'dailyPatientQueue:id,bill_id,queue_number,queue_date',
            ])
            ->get(["id", "system_amount", "bill_amount", "patient_id", "doctor_id"]);

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
            'status' => 'required|string|in:' . Bill::STATUS_RECEPTION,
            'bill_amount' => 'required|numeric|min:0',
        ]);

        try {
            // Find the bill by ID
            $bill = Bill::findOrFail($billId);

            // Update the bill's status and bill amount
            $bill->status = $validatedData['status'];
            $bill->bill_amount = $validatedData['bill_amount'];
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

        if (!$bill = Bill::find($billId)) {
            return new JsonResponse(['message' => 'Bill not found'], 404);
        }

        if ($validated['status'] === Bill::STATUS_PHARMACY) {
            $this->insertNewBillItemForMedicineIfNotExists($billId);
        }

        $bill->status = $validated['status'];
        $bill->save();

        return new JsonResponse(['message' => 'Bill status updated successfully', 'bill' => $bill], 200);
    }

    private function insertNewBillItemForMedicineIfNotExists($billId): void
    {
        BillItem::firstOrCreate(['bill_id' => $billId, 'service_id' => Service::where('key', Service::MEDICINE_KEY)->first()->id]);
    }

    private function createDailyPatientQueue($billId, $doctorId): int
    {
        $today = date('Y-m-d');

        $latestRecord = DailyPatientQueue::where('doctor_id', $doctorId)->where('queue_date', $today)->orderByDesc('id')->first();

        $newRecord = new DailyPatientQueue();
        $newRecord->bill_id = $billId;
        $newRecord->doctor_id = $doctorId;
        $newRecord->queue_date = $today;
        $newRecord->queue_number = $latestRecord ? $latestRecord->queue_number + 1 : 1;
        $newRecord->order_number = $latestRecord ? $latestRecord->order_number + 1 : 1;
        $newRecord->save();

        return $newRecord->queue_number;

    }

    public function bookings($time = null): JsonResponse
    {
        $bookingsQuery = Bill::where('status', Bill::STATUS_BOOKED)
            ->with(['doctor:id,name', 'patient:id,name', 'dailyPatientQueue:id,bill_id,queue_number,queue_date']);
        if ($time === self::OLD_BOOKING_KEYWORD) {
            $bookingsQuery->where('created_at', '<', now()->subDays());
        } else {
            $bookingsQuery->where('created_at', '>=', now()->subDays());
        }

        $bookings = $bookingsQuery->get(['id', 'doctor_id', 'patient_id', 'bill_amount']);

        return new JsonResponse(BookingListResource::collection($bookings));
    }

    public function changeTempBillStatus(ChangeBillStatusRequest $request, $billId): JsonResponse
    {
        $validatedData = $request->validated();

        $status = $validatedData['is_booking'] ? Bill::STATUS_BOOKED : ($validatedData['doctor_id'] ? Bill::STATUS_DOCTOR : Bill::STATUS_PHARMACY);

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
            $bill = Bill::where('status', Bill::STATUS_BOOKED)->where('id', $id)->first();
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

    private function getBillItemsFroPrint($id)
    {
        $billItems = BillItem::where('bill_id', $id)->select(['bill_amount', 'system_amount', 'service_id'])->with('service')->get();

        return $billItems->flatMap(function ($item) {
            return $this->preparePrintData($item->service, $item->bill_amount, $item->system_amount);
        })->toArray();
    }

    private function insertBillItems($serviceId, $billAmount, $systemAmount, $billId): void
    {
        $data = [['bill_id' => $billId, 'service_id' => $serviceId, 'bill_amount' => $billAmount, 'system_amount' => $systemAmount]];
        BillItem::insert($data);
    }

    private function getService($serviceType)
    {

        $serviceKey = match ($serviceType) {
            'channeling' => Service::DEFAULT_SPECIALIST_CHANNELING_KEY,
            'opd' => Service::DEFAULT_DOCTOR_KEY,
            'dental' => Service::DENTAL_REGISTRATION_KEY
        };

        return Service::where('key', $serviceKey)->first();

    }

    /**
     * @param $service
     * @param $billAmount
     * @param int $systemAmount
     * @return array
     *
     * If seperated fields required, need to add two different records in the bill
     */
    public function preparePrintData($service, $billAmount, int $systemAmount = 0): array
    {
        $printingData = [];

        if ($service) {
            $printingData[] = ['name' => $service->name . ' ' . Bill::FEE_ORIGINAL, 'price' => $billAmount];
            if ($service->separate_items) {
                $systemAmount = $systemAmount == 0 ? $this->calculateSystemPrice($service, $billAmount, $systemAmount) : $systemAmount;
                $printingData[] = ['name' => $service->name . ' ' . Bill::FEE_INSTITUTION, 'price' => $systemAmount];
            }
        }
        return $printingData;
    }

}
