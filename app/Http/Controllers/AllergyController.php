<?php
namespace App\Http\Controllers;

use App\Http\Resources\AllergyResource;
use App\Models\Allergy;
use App\Http\Requests\StoreAllergyRequest;
use App\Http\Requests\UpdateAllergyRequest;

class AllergyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allergies = Allergy::all();
        return AllergyResource::collection($allergies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAllergyRequest $request)
    {
        $allergy = Allergy::create($request->validated());

        return new AllergyResource($allergy);
    }

    /**
     * Display the specified resource.
     */
    public function show(Allergy $allergy)
    {
        return new AllergyResource($allergy);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAllergyRequest $request, Allergy $allergy)
    {
        $allergy->update($request->validated());

        return new AllergyResource($allergy);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Allergy $allergy)
    {
        $allergy->delete();

        return response()->json(null, 204);
    }
}
