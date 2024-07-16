<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorsChannelingFeeRequest;
use App\Http\Requests\UpdateDoctorsChannelingFeeRequest;
use App\Http\Resources\DoctorChannelingFeeResource;
use App\Models\DoctorsChannelingFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorsChannelingFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return DoctorChannelingFeeResource::collection(DoctorsChannelingFee::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorsChannelingFeeRequest $request): JsonResponse
    {
        $doctorChannelingFee = DoctorsChannelingFee::create($request->all());
        return new JsonResponse($doctorChannelingFee);

    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorsChannelingFee $doctorsChannelingFee)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorsChannelingFeeRequest $request, DoctorsChannelingFee $doctorsChannelingFee)
    {
        $doctorsChannelingFee->fee = $request->input('fee');
        $doctorsChannelingFee->save();
        return new JsonResponse($doctorsChannelingFee);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorsChannelingFee $doctorsChannelingFee): JsonResponse
    {
        $doctorsChannelingFee->delete();
        return new JsonResponse("Success!");
    }
}
