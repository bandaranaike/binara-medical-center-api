<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiseaseResource;
use App\Models\Disease;
use App\Http\Requests\StoreDiseaseRequest;
use App\Http\Requests\UpdateDiseaseRequest;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $diseases = Disease::all();
        return DiseaseResource::collection($diseases);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiseaseRequest $request)
    {
        $disease = Disease::create($request->validated());

        return new DiseaseResource($disease);
    }

    /**
     * Display the specified resource.
     */
    public function show(Disease $disease)
    {
        return new DiseaseResource($disease);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiseaseRequest $request, Disease $disease)
    {
        $disease->update($request->validated());

        return new DiseaseResource($disease);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disease $disease)
    {
        $disease->delete();

        return response()->json(null, 204);
    }
}
