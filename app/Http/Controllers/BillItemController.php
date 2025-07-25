<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Http\Controllers\Traits\SystemPriceCalculator;
use App\Http\Requests\StoreBillItemRequest;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Service;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillItemController extends Controller
{

    use SystemPriceCalculator;

    private bool $isNewBill = false;

    public function getBillItemsForBill($billId): Collection
    {
        return BillItem::with('service')->where('bill_id', $billId)->get();
    }

    /**
     * Store a new bill item in the database.
     *
     * @param StoreBillItemRequest $request
     * @return JsonResponse
     */
    public function store(StoreBillItemRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $serviceId = $this->createNewServiceIfNotExists($validatedData['service_id'], $validatedData['service_name']);

        $billId = $this->createTempBillIfNotExists($validatedData['bill_id'], $validatedData['patient_id'] ?? null);

        try {
            $billItem = BillItem::create([
                'bill_id' => $billId,
                'service_id' => $serviceId,
                'system_amount' => $validatedData['system_amount'],
                'bill_amount' => $validatedData['bill_amount'],
            ]);

            if ($this->isNewBill) {
                return new JsonResponse($this->getBillForServices($billId), 201);
            }

            return new JsonResponse($billItem->load('service:id,name'), 201);

        } catch (Exception $exception) {
            return new JsonResponse('An error occurred while adding the bill item. ' . $exception->getMessage(), 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validatedData = $request->validate([
            'bill_amount' => 'required_if:system_amount,null|numeric|min:0',
            'system_amount' => 'required_if:bill_amount,null|numeric|min:0',
        ]);

        try {
            $billItem = BillItem::findOrFail($id);
            if (isset($validatedData['bill_amount'])) $billItem->bill_amount = $validatedData['bill_amount'];
            if (isset($validatedData['system_amount'])) $billItem->system_amount = $validatedData['system_amount'];
            $billItem->save();

            return new JsonResponse(['message' => 'Bill item updated successfully', 'data' => $billItem]);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Error updating bill item', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            // Find the bill item by ID
            $billItem = BillItem::findOrFail($id);

            // Delete the bill item
            $billItem->delete();

            return new JsonResponse([
                'success' => true,
                'message' => 'Bill item deleted successfully.',
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
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
            $this->isNewBill = true;
            $billId = Bill::create(['patient_id' => $patientId, 'status' => BillStatus::TREATMENT])->id;
        }
        return $billId;
    }

    public function getBillForServices($billId): Bill
    {
        return Bill::where('status', BillStatus::TREATMENT)
            ->where('id', $billId)
            ->with(['billItems' => function ($query) {
                $query->with('service:id,name')
                    ->select('id', 'bill_id', 'service_id', 'bill_amount');
            }])->first();
    }

}
