<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PatientsAllergyController extends Controller
{
    /**
     * Add a new allergy to a patient.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addAllergy(Request $request): JsonResponse
    {
        // Validate the request data
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id', // Ensure patient exists
            'allergy_name' => 'required|string|max:255', // Ensure allergy name is provided
        ]);

        // Find the patient by ID
        $patient = Patient::find($validatedData['patient_id']);

        if (!$patient) {
            return new JsonResponse([
                'message' => 'Patient not found',
            ], 404);
        }

        // Check if the allergy already exists or create a new one
        $allergy = Allergy::firstOrCreate(
            ['name' => $validatedData['allergy_name']]
        );

        // Attach the allergy to the patient if it's not already attached
        if (!$patient->allergies()->where('allergy_id', $allergy->id)->exists()) {
            $patient->allergies()->attach($allergy->id);
        } else {
            return new JsonResponse(['added' => false, 'message' => 'Allergy already exists.'], 409);
        }

        // Return the newly added allergy
        return new JsonResponse([
            'id' => $allergy->id,
            'name' => $allergy->name,
            'message' => 'Allergy added successfully',
        ], 201);
    }

    /**
     * Remove an allergy from a patient.
     *
     * @param Request $request
     * @param int $allergyId
     * @return JsonResponse
     */
    public function removeAllergy(Request $request, int $allergyId): JsonResponse
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

        // Check if the allergy exists and is associated with the patient
        if (!$patient->allergies()->where('allergy_id', $allergyId)->exists()) {
            return new JsonResponse([
                'message' => 'Allergy not associated with this patient',
            ], 404);
        }

        // Detach (remove) the allergy from the patient
        $patient->allergies()->detach($allergyId);

        return new JsonResponse([
            'message' => 'Allergy removed successfully',
        ], 200);
    }
}
