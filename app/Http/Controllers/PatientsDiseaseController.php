<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Psy\Util\Json;

class PatientsDiseaseController extends Controller
{
    /**
     * Add a new disease to a patient.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id', // Ensure patient exists
            'disease_name' => 'required|string|max:255', // Ensure disease name is provided
        ]);

        // Find the patient by ID
        $patient = Patient::find($validatedData['patient_id']);

        if (!$patient) {
            return new JsonResponse([
                'message' => 'Patient not found',
            ], 404);
        }

        // Check if the disease already exists or create a new one
        $disease = Disease::firstOrCreate(
            ['name' => $validatedData['disease_name']]
        );

        // Attach the disease to the patient if it's not already attached
        if (!$patient->diseases()->where('disease_id', $disease->id)->exists()) {
            $patient->diseases()->attach($disease->id);
        } else {
            return new JsonResponse(['added' => false, 'message' => 'Disease already exists'], 409);
        }

        // Return the newly added disease
        return new JsonResponse([
            'id' => $disease->id,
            'name' => $disease->name,
            'message' => 'Disease added successfully',
        ], 201);
    }

    /**
     * Remove a disease from a patient.
     *
     * @param Request $request
     * @param int $diseaseId
     * @return JsonResponse
     */
    public function removeDisease(Request $request, $diseaseId)
    {
        // Validate the request to ensure the patient_id is present
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id', // Ensure patient exists
        ]);

        // Find the patient by ID
        $patient = Patient::find($validatedData['patient_id']);

        if (!$patient) {
            return new JsonResponse([
                'message' => 'Patient not found',
            ], 404);
        }

        // Check if the disease exists and is associated with the patient
        if (!$patient->diseases()->where('disease_id', $diseaseId)->exists()) {
            return new JsonResponse([
                'message' => 'Disease not associated with this patient',
            ], 404);
        }

        // Detach (remove) the disease from the patient
        $patient->diseases()->detach($diseaseId);

        return new JsonResponse([
            'message' => 'Disease removed successfully',
        ], 200);
    }
}
