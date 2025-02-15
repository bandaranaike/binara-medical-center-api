<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $search = Str::replace(['0'], '+94', $search);

        $patients = User::where('phone', 'LIKE', '%' . $search . '%')
            ->orWhere('name', 'LIKE', '%' . $search . '%')
            ->whereHas('role', function ($query) {
                $query->where('key', UserRole::PATIENT);
            })
            ->with('patients', function ($query) {
                $query->select(['id', 'name', 'telephone', 'age', 'gender', 'birthday', 'address', 'email', 'user_id']);
            })
            ->get(['id', 'name', 'phone']);

        return new JsonResponse($patients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request): PatientResource
    {
        $patient = Patient::create($request->validated());

        $user_id = $this->createUserIfNotExitsForPatient($patient);

        $patient->user_id = $user_id;
        $patient->save();

        return new PatientResource($patient);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): JsonResponse
    {
        return new JsonResponse($patient);
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

    private function createUserIfNotExitsForPatient($patient)
    {
        $role = Role::where('key', UserRole::PATIENT->value)->first();

        $user = User::where(function ($query) use ($patient) {
            $query->where('phone', $patient->telephone)->whereNotNull('phone');
        })->orWhere(function ($query) use ($patient) {
            $query->where('email', $patient->email)->whereNotNull('email');
        })->first();

        if (!$user) {
            $user = User::create([
                "name" => $patient->name,
                "role_id" => $role->id,
                "phone" => $patient->telephone,
                "email" => $patient->email,
                "password" => Hash::make(Str::random(8)),
            ]);
        }
        return $user->id;
    }

    public function usersPatientsListForWeb(Request $request)
    {
        return Patient::whereIn('id', $request->get('ensure_middleware_patient_ids'))
            ->select(['id', 'name', 'telephone', 'age', 'gender', 'birthday', 'address', 'email'])
            ->get();
    }

    public function loggedUserDetailsForWeb(Request $request)
    {
        return User::find(Auth::id());
    }
}
