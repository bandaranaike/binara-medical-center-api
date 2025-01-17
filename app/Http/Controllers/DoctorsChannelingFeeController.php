<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\SystemPriceCalculator;
use App\Http\Requests\StoreDoctorsChannelingFeeRequest;
use App\Http\Requests\UpdateDoctorsChannelingFeeRequest;
use App\Http\Resources\DoctorChannelingFeeResource;
use App\Models\Doctor;
use App\Models\DoctorsChannelingFee;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorsChannelingFeeController extends Controller
{
    use SystemPriceCalculator;

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
    public function getFee($id): JsonResponse
    {
        /**
         * If the doctor is specialist : get the channeling fee
         * If the doctor is opd : get the channeling fee
         * If the doctor is dental : get the dental registration fee
         */

        $doctor = Doctor::with('channellingFee:doctor_id,fee')->select(['id', 'doctor_type', 'name'])->find($id);

        $defaultServiceKey = match ($doctor->doctor_type) {
            Doctor::DOCTOR_TYPE_DENTAL => Service::DENTAL_REGISTRATION_KEY,
            Doctor::DOCTOR_TYPE_OPD => Service::DEFAULT_DOCTOR_KEY,
            Doctor::DOCTOR_TYPE_SPECIALIST => Service::DEFAULT_SPECIALIST_CHANNELING_KEY,
        };

        $service = Service::getByKey($defaultServiceKey)->first();

        if ($doctor->channellingFee) {
            $fee = $doctor->channellingFee->fee;
        } else {
            $fee = $service?->bill_price;
        }

        [$billPrice, $systemPrice] = $this->getBillPriceAndSystemPrice($service, $fee);

        return new JsonResponse(['bill_price' => $billPrice, 'system_price' => $systemPrice, 'name' => $doctor->name]);
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
