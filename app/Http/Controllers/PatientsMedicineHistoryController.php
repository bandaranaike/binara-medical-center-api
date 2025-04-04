<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Events\AddedDrugForBill;
use App\Events\RemovedDrugFromBill;
use App\Exceptions\InsufficientStocksException;
use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Controllers\Traits\StockTrait;
use App\Http\Requests\StorePatientMedicineRequest;
use App\Models\Bill;
use App\Models\MedicationFrequency;
use App\Models\Medicine;
use App\Models\PatientMedicineHistory;
use Exception;
use Illuminate\Http\JsonResponse;

class PatientsMedicineHistoryController extends Controller
{
    use BillItemsTrait, StockTrait;

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $patientMedicineHistory = PatientMedicineHistory::findOrFail($id);

        $this->removeSaleItem($patientMedicineHistory->sale_id);

        $patientMedicineHistory->delete();

        return new JsonResponse([
            'message' => 'Patient-Medicine record deleted successfully.',
        ]);
    }

    public function getMedicineHistories($patientId): JsonResponse
    {
        try {
            $medicineHistories = Bill::where("patient_id", $patientId)
                ->where("status", BillStatus::DONE)
                ->orderBy("id", "desc")
                ->limit(10)
                ->get(['id', 'created_at']);

            return new JsonResponse(($medicineHistories));
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Failed to retrieve medicine histories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StorePatientMedicineRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $brandId = $validated['brand_id']; // Get the brand id
            $medicationFrequencyId = $this->createNewMedicationFrequencyIfNotExists(
                $validated['medication_frequency_id'], $validated['medication_frequency_name']
            );

            // Get the logged-in user id then related doctor id (the middleware handles)
            $doctorId = $request->doctor_id;

            $saleId = $this->addSaleItem($brandId, $validated['quantity'], $validated['bill_id']);

            // Insert the new medicine record
            PatientMedicineHistory::create([
                'patient_id' => $validated['patient_id'],
                'bill_id' => $validated['bill_id'],
                'doctor_id' => $doctorId,
                'medication_frequency_id' => $medicationFrequencyId,
                'sale_id' => $saleId,
                'duration' => $validated['duration'],
            ]);

            // Adjust the stock

            $isMedicineItemAdded = $this->createMedicineBillItemIfNotExists($validated['bill_id']);

            // Return the transformed response using the collection
            return new JsonResponse([
                'message' => 'Medicine added successfully',
                'added_medicine_item' => $isMedicineItemAdded,
            ], 201);

        } catch (InsufficientStocksException $e) {
            return new JsonResponse([
                'message' => "Failed to add medicine",
                'error' => $e->getMessage(),
            ], 409);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Failed to add medicine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function createNewMedicineIfNotExists($medicineId, $medicineName)
    {
        if ($medicineId === "-1") {
            $medicineId = Medicine::create(["name" => $medicineName])->id;
        }
        return $medicineId;
    }

    private function createNewMedicationFrequencyIfNotExists(mixed $medicationFrequencyId, mixed $medicationFrequencyName)
    {
        if ($medicationFrequencyId === "-1") {
            $medicationFrequencyId = MedicationFrequency::create(["name" => $medicationFrequencyName])->id;
        }
        return $medicationFrequencyId;
    }

    public function getHistoryForABill($billId): JsonResponse
    {
        $patientMedicineHistories = PatientMedicineHistory::where('bill_id', $billId)
            ->with('medicationFrequency:id,name', 'sale.brand.drug:id,name', 'sale.brand:id,name,drug_id', 'sale:id,brand_id,quantity,total_price')
            ->get(["id", "medication_frequency_id", "duration", 'sale_id']);

        return new JsonResponse($patientMedicineHistories);
    }
}
