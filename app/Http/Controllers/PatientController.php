<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $patients = Patient::query();

        if ($request->has('search')) {
            $patients->where('telephone', 'LIKE', '%' . $request->get('search') . '%');
        }
        $patients = $patients->get();
        return PatientResource::collection($patients);
    }

    public function getDropdownList(StorePatientRequest $request): PatientResource
    {
        $patients = Patient::query();

        if ($request->has('search')) {
            $patients->where('telephone', 'LIKE', '%' . $request->get('search') . '%');
        }
        $patients = $patients->get();
        return PatientResource::collection($patients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request): PatientResource
    {
        $patient = Patient::create($request->validated());

        return new PatientResource($patient);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): PatientResource
    {
        return new PatientResource($patient);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): PatientResource
    {
        $patient->update($request->validated());

        return new PatientResource($patient);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient): \Illuminate\Http\JsonResponse
    {
        $patient->delete();

        return response()->json(null, 204);
    }
}
