<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\SearchPublicPatientRequest;
use App\Http\Requests\PublicApi\StorePublicPatientRequest;
use App\Http\Requests\PublicApi\UpdatePublicPatientRequest;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PublicPatientController extends Controller
{
    public function search(SearchPublicPatientRequest $request): JsonResponse
    {
        $query = trim($request->validated('query'));
        $normalizedQuery = $this->normalizePhone($query);
        $likeQuery = '%'.$query.'%';
        $normalizedLikeQuery = '%'.$normalizedQuery.'%';

        $patients = Patient::query()
            ->select(['id', 'name', 'telephone', 'email', 'age', 'gender', 'address', 'birthday'])
            ->where(function ($builder) use ($likeQuery, $normalizedLikeQuery) {
                $builder->where('telephone', 'like', $likeQuery)
                    ->orWhere('telephone', 'like', $normalizedLikeQuery)
                    ->orWhere('name', 'like', $likeQuery);
            })
            ->orderByRaw(
                'case
                    when telephone = ? then 0
                    when telephone = ? then 1
                    when telephone like ? then 2
                    when telephone like ? then 3
                    when name = ? then 4
                    when name like ? then 5
                    else 6
                end',
                [
                    $normalizedQuery,
                    $query,
                    $normalizedQuery.'%',
                    $query.'%',
                    $query,
                    $likeQuery,
                ]
            )
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $patients,
        ]);
    }

    public function store(StorePublicPatientRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['telephone'] = $this->normalizePhone($payload['telephone']);

        if (Patient::query()->where('telephone', $payload['telephone'])->exists()) {
            return response()->json([
                'message' => 'Patient already exists for the given telephone number.',
            ], 409);
        }

        $patient = Patient::create($payload);

        return response()->json($this->serializePatient($patient), 201);
    }

    public function update(UpdatePublicPatientRequest $request, Patient $patient): JsonResponse
    {
        $payload = $request->validated();

        if (array_key_exists('telephone', $payload)) {
            $payload['telephone'] = $this->normalizePhone($payload['telephone']);
        }

        $patient->update($payload);

        return response()->json($this->serializePatient($patient->fresh()));
    }

    public function upsert(StorePublicPatientRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['telephone'] = $this->normalizePhone($payload['telephone']);

        $patient = Patient::query()->where('telephone', $payload['telephone'])->first();

        if ($patient) {
            $patient->update($payload);

            return response()->json([
                'action' => 'updated',
                'patient' => $this->serializePatient($patient->fresh()),
            ]);
        }

        $patient = Patient::create($payload);

        return response()->json([
            'action' => 'created',
            'patient' => $this->serializePatient($patient),
        ]);
    }

    private function normalizePhone(string $telephone): string
    {
        return Str::replaceMatches('/^0/', '+94', trim($telephone));
    }

    private function serializePatient(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->name,
            'telephone' => $patient->telephone,
            'email' => $patient->email,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'address' => $patient->address,
            'birthday' => $patient->birthday,
        ];
    }
}
