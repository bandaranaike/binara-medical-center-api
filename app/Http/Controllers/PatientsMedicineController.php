<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientMedicineRequest;
use App\Http\Requests\UpdatePatientMedicineRequest;
use App\Models\BillItem;
use App\Models\PatientsMedicine;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class PatientsMedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $patientMedicines = PatientsMedicine::all();

        return response()->json([
            'message' => 'Patient-Medicines retrieved successfully.',
            'data' => $patientMedicines,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientMedicineRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Check if a bill item exists for the given bill_id and type 'medicine'
        $billItem = BillItem::firstOrCreate(
            [
                'bill_id' => $validatedData['bill_id'],
                'service_id' => Service::where('key', config('binara.services.keys.medicine'))->get()->first()->id,
            ]
        );
//dd($validatedData);
        // Attach the medicine to the newly created or existing bill item
        $patientMedicine = PatientsMedicine::create(array_merge($validatedData, [
            'bill_item_id' => $billItem->id,
        ]));

        // Update the bill item price and quantity based on associated patient medicines
        $totalPrice = PatientsMedicine::where('bill_item_id', $billItem->id)->sum('price');
        $billItem->update([
            'price' => $totalPrice,
        ]);

        return response()->json([
            'message' => 'Patient-Medicine record created successfully.',
            'data' => $patientMedicine,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(PatientsMedicine $patientMedicine): JsonResponse
    {
        return response()->json([
            'message' => 'Patient-Medicine retrieved successfully.',
            'data' => $patientMedicine,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientMedicineRequest $request, PatientsMedicine $patientMedicine): JsonResponse
    {
        $validatedData = $request->validated();

        $patientMedicine->update($validatedData);

        return response()->json([
            'message' => 'Patient-Medicine record updated successfully.',
            'data' => $patientMedicine,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientsMedicine $patientMedicine): JsonResponse
    {
        $patientMedicine->delete();

        return response()->json([
            'message' => 'Patient-Medicine record deleted successfully.',
        ], 200);
    }
}
