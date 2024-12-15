<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $patients = Patient::query();

        if ($request->has('search')) {
            $patients->where('telephone', 'LIKE', '%' . $request->get('search') . '%');
        }
        $patients = $patients->get();
        return PatientResource::collection($patients);
    }

    /**
     * Display a listing of the resource.
     */
    public function search(Request $request)
    {
        $search = $request->get('query');

        $patients = Patient::where('telephone', 'LIKE', '%' . $search . '%')
            ->orWhere('name', 'LIKE', '%' . $search . '%')
            ->get(['id', 'name', 'telephone', 'age', 'gender', 'birthday', 'address', 'email']);

        return new JsonResponse($patients);
    }


    public function getPatientDataByTelephone($telephone): JsonResponse
    {
        $patients = Patient::where('telephone', $telephone)->first();
        return new JsonResponse($patients);
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
