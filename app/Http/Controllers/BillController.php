<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $bill = Bill::create($request->validated());
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
    public function getPendingBills(): JsonResponse
    {
        $pendingBills = Bill::where('status', 'pending')
            ->with(['patient', 'patient.allergies', 'patient.diseases'])
            ->with('patient.patientHistories', function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->with('patient.bills.patientMedicineBillItem', function ($query) {
                $query->with('patientMedicines.medicine');
            })
            ->get();

        return new JsonResponse($pendingBills);
    }

    /**
     * Get all pending bills.
     *
     * @return JsonResponse
     */
    public function getPendingInvoices(): JsonResponse
    {
        $pendingBills = Bill::where('status', 'pending')
            ->with(['patient' => function ($query) {
                $query->select('id', 'name'); // Load only necessary patient fields
            }])
            ->with(['billItems' => function ($query) {
                $query->with(['patientMedicines' => function ($query) {
                    $query->select('id', 'bill_item_id', 'medicine_id', 'price') // Load medicines related to bill items
                    ->with('medicine');
                }])
                    ->with('service:id,name')
                    ->select('id', 'bill_id', 'service_id', 'bill_amount'); // Load only necessary fields for bill items
            }])
            ->get(['id', 'patient_id', 'status']); // Load only necessary fields for bills

        return response()->json($pendingBills);
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
        // Validate the incoming request
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
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to finalize the bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
