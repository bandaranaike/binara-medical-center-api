<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillItemRequest;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Service;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillItemController extends Controller
{
    /**
     * Store a new bill item in the database.
     *
     * @param StoreBillItemRequest $request
     * @return JsonResponse
     */
    public function store(StoreBillItemRequest $request): JsonResponse
    {
        // Validate the incoming request
        $validatedData = $request->validated();

        $serviceId = $this->createNewServiceIfNotExists($validatedData['service_id'], $validatedData['service_name']);
        $billId = $this->createTempBillIfNotExists($validatedData['bill_id'], $validatedData['patient_id']);

        try {
            // Create the new BillItem
            $billItem = BillItem::create([
                'bill_id' => $billId,
                'service_id' => $serviceId,
                'system_amount' => Service::where('id', $serviceId)->first()->bill_price,
                'bill_amount' => $validatedData['bill_amount'],
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Bill item added successfully.',
                'data' => $billItem->load('service:id,name'),
            ], 201);

        } catch (Exception $e) {
            // Handle any exceptions that may occur
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the bill item.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validatedData = $request->validate([
            'bill_amount' => 'required|numeric|min:0',
        ]);

        try {
            $billItem = BillItem::findOrFail($id);
            $billItem->bill_amount = $validatedData['bill_amount'];
            $billItem->save();

            return response()->json(['message' => 'Bill item updated successfully', 'data' => $billItem]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error updating bill item', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            // Find the bill item by ID
            $billItem = BillItem::findOrFail($id);

            // Delete the bill item
            $billItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bill item deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting bill item: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function createNewServiceIfNotExists($serviceId, $serviceName)
    {
        if ($serviceId === "-1") {
            $serviceId = Service::create(["name" => $serviceName, 'key' => Str::random(8)])->id;
        }
        return $serviceId;
    }

    private function createTempBillIfNotExists($billId, $patientId)
    {
        if ($billId === -1) {
            $billId = Bill::create(['patient_id' => $patientId, 'status' => Bill::STATUS_TEMPORARY])->id;
        }
        return $billId;
    }

}
