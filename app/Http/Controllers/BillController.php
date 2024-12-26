<?php

namespace App\Http\Controllers;

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

        $system_amount = $this->getSystemAmount($request->input('is_opd'));

        $bill = Bill::create([...$request->validated(), 'status' => $status, 'system_amount' => $system_amount]);

        $this->createDailyPatientQueue($bill->id, $request->input('doctor_id'));

        return new JsonResponse($bill->id);
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
            ->join('daily_patient_queues', 'bills.id', '=', 'daily_patient_queues.bill_id')
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
        $pendingBills = Bill::where('status', Bill::STATUS_PENDING)
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
    public function finalizeBill(Request $request, int $billId): JsonResponse
    {
        $validatedData = $request->validate([
            'status' => 'required|string|in:done',
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

    public function updateStatus($billId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $bill = Bill::find($billId);

        if (!$bill) {
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

    private function createDailyPatientQueue($billId, $doctorId): void
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

    }

    public function bookings(): JsonResponse
    {
        $bookings = Bill::where('status', Bill::STATUS_BOOKED)
            ->with(['doctor:id,name', 'patient:id,name', 'dailyPatientQueue:id,bill_id,queue_number,queue_date'])
            ->get(['id', 'doctor_id', 'patient_id', 'bill_amount']);

        return new JsonResponse(BookingListResource::collection($bookings));
    }

    private function getSystemAmount($isOpd)
    {
        return $isOpd ?
            Service::where('key', Service::DEFAULT_DOCTOR_KEY)->first()->system_price :
            Service::where('key', Service::DEFAULT_SPECIALIST_CHANNELING_KEY)->first()->system_price;
    }

    private function changeBillStatus()
    {

    }
}
