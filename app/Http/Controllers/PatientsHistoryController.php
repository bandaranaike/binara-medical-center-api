<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientsHistoryRequest;
use App\Http\Resources\PatientsHistoryResource;
use App\Models\Patient;
use App\Models\PatientsHistory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientsHistoryController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientsHistoryRequest $request): PatientsHistoryResource
    {
        $history = PatientsHistory::create([...$request->validated(), 'doctor_id' => Auth::id()]);

        return new PatientsHistoryResource($history);
    }

    /**
     * Get the history of a specific patient for a specific doctor.
     *
     * @param int $patientId
     * @param int $doctorId
     * @return JsonResponse
     */
    public function getPatientHistory(int $patientId): JsonResponse
    {
        try {
            // Check if the patient exists
            $patient = Patient::find($patientId);

            $doctorId = Auth::id();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found'
                ], 404);
            }

            // Fetch the patient's history for the given doctor
            $history = PatientsHistory::where('patient_id', $patientId)
                ->where('doctor_id', $doctorId)
                ->select(['id', 'note'])
                ->selectRaw('SUBSTRING(created_at, 1, 10) AS date')
                ->get();

            if ($history->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No history found for this patient with this doctor',
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Patient history retrieved successfully',
                'data' => $history
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching patient history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
