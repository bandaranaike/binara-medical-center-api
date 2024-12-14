<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorsChannelingFeeRequest;
use App\Http\Requests\UpdateDoctorsChannelingFeeRequest;
use App\Http\Resources\DoctorChannelingFeeResource;
use App\Models\DoctorsChannelingFee;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Config;

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
    public function show(DoctorsChannelingFee $doctorsChannelingFee): JsonResponse
    {
        return new JsonResponse($doctorsChannelingFee);
    }


    /**
     * Display the specified resource.s
     */
    public function getFee($id, $isOPD = false): JsonResponse
    {
        $doctorsChannelingFee = DoctorsChannelingFee::find($id);

        if ($doctorsChannelingFee) {
            $fee = $doctorsChannelingFee->fee;
        } else {
            $defaultServiceKey = $isOPD ? Service::DEFAULT_DOCTOR_KEY : Service::DEFAULT_SPECIALIST_CHANNELING_KEY;
            $fee = Service::getByKey($defaultServiceKey)->first()?->bill_price;
        }
        return new JsonResponse($fee);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorsChannelingFeeRequest $request, DoctorsChannelingFee $doctorsChannelingFee): JsonResponse
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
