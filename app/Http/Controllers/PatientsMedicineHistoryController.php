<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientMedicineRequest;
use App\Http\Resources\PatientMedicineHistoryCollection;
use App\Models\Bill;
use App\Models\Medicine;
use App\Models\PatientMedicineHistory;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientsMedicineHistoryController extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientMedicineHistory $patientMedicine): JsonResponse
    {
        $patientMedicine->delete();

        return response()->json([
            'message' => 'Patient-Medicine record deleted successfully.',
        ]);
    }

    public function getMedicineHistories($patientId): JsonResponse
    {
        try {
            // Get the logged-in doctor's ID
            $doctorId = Auth::id();

            $medicineHistories = $this->getDoctorsPendingMedicines($patientId, $doctorId);

            return new JsonResponse(new PatientMedicineHistoryCollection($medicineHistories));
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

            // Get the logged-in doctor's ID
            $doctorId = $request->doctor_id;

            // Insert the new medicine record
            PatientMedicineHistory::create([
                'patient_id' => $validated['patient_id'],
                'bill_id' => $validated['bill_id'],
                'doctor_id' => $doctorId,
                'medicine_id' => $medicineId,
                'dosage' => $validated['dosage'],
                'type' => $validated['type'],
                'duration' => $validated['duration'],
            ]);

            // Fetch the updated list of medicine histories for the same patient
            $updatedHistories = $this->getDoctorsPendingMedicines($validated['patient_id'], $doctorId);

            // Return the transformed response using the collection
            return response()->json([
                'message' => 'Medicine added successfully',
                'data' => new PatientMedicineHistoryCollection($updatedHistories),
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

    private function getDoctorsPendingMedicines($patientId, $doctorId): Collection
    {
        return PatientMedicineHistory::with(['medicine', 'bill'])
            ->where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->whereHas('bill', function ($query) {
                $query->where("status", Bill::STATUS_DOCTOR);
            })
            ->get();
    }

}
