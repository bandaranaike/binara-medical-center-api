<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Http\Controllers\Traits\BillItemsTrait;
use App\Http\Requests\StorePatientMedicineRequest;
use App\Models\Bill;
use App\Models\MedicationFrequency;
use App\Models\Medicine;
use App\Models\PatientMedicineHistory;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class PatientsMedicineHistoryController extends Controller
{
    use BillItemsTrait;

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        PatientMedicineHistory::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Patient-Medicine record deleted successfully.',
        ]);
    }

    public function getMedicineHistories($patientId): JsonResponse
    {
        try {
            // Get the doctor's ID from the middleware ensure.doctor
            $doctorId = request('doctor_id');

            $medicineHistories = $this->getDoctorsPatientMedicineHistories($patientId, $doctorId);

            return new JsonResponse(($medicineHistories));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve medicine histories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StorePatientMedicineRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $medicineId = $this->createNewMedicineIfNotExists($validated['medicine_id'], $validated['medicine_name']);
            $medicationFrequencyId = $this->createNewMedicationFrequencyIfNotExists(
                $validated['medication_frequency_id'], $validated['medication_frequency_name']
            );

            // Get the logged-in user id then related doctor id (the middleware handles)
            $doctorId = $request->doctor_id;

            // Insert the new medicine record
            PatientMedicineHistory::create([
                'patient_id' => $validated['patient_id'],
                'bill_id' => $validated['bill_id'],
                'doctor_id' => $doctorId,
                'medicine_id' => $medicineId,
                'medication_frequency_id' => $medicationFrequencyId,
                'duration' => $validated['duration'],
            ]);

            $isMedicineItemAdded = $this->createMedicineBillItemIfNotExists($validated['bill_id']);

            // Return the transformed response using the collection
            return response()->json([
                'message' => 'Medicine added successfully',
                'is_medicine_item_added' => $isMedicineItemAdded,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
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

    private function getDoctorsPatientMedicineHistories($patientId, $doctorId): Collection
    {
        return Bill::where("patient_id", $patientId)
            ->where("doctor_id", $doctorId)
            ->where("status", BillStatus::DONE)
            ->orderBy("id", "desc")
            ->limit(10)
            ->get(['id', 'created_at']);
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
            ->with('medicine:id,name', 'medicationFrequency:id,name')
            ->get(["id", "medicine_id", "medication_frequency_id", "duration"]);

        return new JsonResponse($patientMedicineHistories);
    }
}
