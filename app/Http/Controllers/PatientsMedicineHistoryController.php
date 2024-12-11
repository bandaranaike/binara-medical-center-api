<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePatientMedicineRequest;
use App\Http\Resources\PatientMedicineHistoryCollection;
use App\Models\PatientMedicineHistory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

            $medicineHistories = PatientMedicineHistory::with(['medicine', 'bill'])
                ->where('patient_id', $patientId)
                ->where('doctor_id', $doctorId)
                ->get();

            return new JsonResponse(new PatientMedicineHistoryCollection($medicineHistories));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve medicine histories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'bill_id' => 'required|exists:bills,id',
                'medicine_id' => 'required|exists:medicines,id',
                'dosage' => 'nullable|string',
                'type' => 'nullable|string',
                'duration' => 'nullable|string',
            ]);

            // Get the logged-in doctor's ID
            $doctorId = Auth::id();

            // Insert the new medicine record
            PatientMedicineHistory::create([
                'patient_id' => $validated['patient_id'],
                'bill_id' => $validated['bill_id'],
                'doctor_id' => $doctorId,
                'medicine_id' => $validated['medicine_id'],
                'dosage' => $validated['dosage'],
                'type' => $validated['type'],
                'duration' => $validated['duration'],
            ]);

            // Fetch the updated list of medicine histories for the same patient
            $updatedHistories = PatientMedicineHistory::with(['medicine', 'bill'])
                ->where('patient_id', $validated['patient_id'])
                ->get();

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
}
