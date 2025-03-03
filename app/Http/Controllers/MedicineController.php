<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use Illuminate\Http\JsonResponse;

class MedicineController extends Controller
{
    /**
     * Store a newly created medicine in storage.
     *
     * @param StoreMedicineRequest $request
     * @return JsonResponse
     */
    public function store(StoreMedicineRequest $request): JsonResponse
    {
        // Create the medicine record
        $medicine = Medicine::create( $request->validated());

        return new JsonResponse([
            'message' => 'Medicine created successfully.',
            'data' => $medicine,
        ], 201);
    }

    /**
     * Update the specified medicine in storage.
     *
     * @param UpdateMedicineRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMedicineRequest $request, int $id): JsonResponse
    {
        // Find the medicine by ID
        $medicine = Medicine::find($id);

        if (!$medicine) {
            return new JsonResponse([
                'message' => 'Medicine not found.',
            ], 404);
        }

        // Update the medicine record
        $medicine->update($request->validated());

        return new JsonResponse([
            'message' => 'Medicine updated successfully.',
            'data' => $medicine,
        ], 200);
    }
}
