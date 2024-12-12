<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillItemRequest;
use App\Models\BillItem;
use App\Models\Medicine;
use App\Models\Service;
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

        try {
            // Create the new BillItem
            $billItem = BillItem::create([
                'bill_id' => $validatedData['bill_id'],
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

        } catch (\Exception $e) {
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

            return response()->json(['message' => 'Bill item updated successfully', 'data' => $billItem], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating bill item', 'error' => $e->getMessage()], 500);
        }
    }

    private function createNewServiceIfNotExists($serviceId, $serviceName)
    {
        if ($serviceId === "-1") {
            $serviceId = Service::create(["name" => $serviceName, 'key' => Str::random(8)])->id;
        }
        return $serviceId;
    }

}
