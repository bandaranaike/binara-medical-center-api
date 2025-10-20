<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Http\Resources\DoctorScheduleResource;
use App\Models\DoctorAvailability;
use App\Models\DoctorSchedule;
use App\Services\DoctorScheduleService;
use Illuminate\Http\JsonResponse;

class DoctorScheduleController extends Controller
{
    use CrudTrait {
        store as traitStore;
        update as traitUpdate;
        destroy as traitDestroy;
    }

    public function __construct(protected DoctorScheduleService $doctorScheduleService)
    {
        $this->model = new DoctorSchedule();
        $this->updateRequest = new UpdateDoctorScheduleRequest();
        $this->storeRequest = new StoreDoctorScheduleRequest();
        $this->resource = DoctorScheduleResource::class;
        $this->relationships = ['doctor:id,name'];
    }

    public function store(StoreDoctorScheduleRequest $request): JsonResponse
    {
        $doctorId = $request->input('doctor_id');
        $response = $this->traitStore($request);
        $this->doctorScheduleService->generateAvailabilityForDoctorForMonth($doctorId);
        return $response;
    }

    public function update(UpdateDoctorScheduleRequest $request, $id): JsonResponse
    {
        $doctorId = $request->input('doctor_id');
        $response = $this->traitUpdate($request, $id);
        $this->doctorScheduleService->generateAvailabilityForDoctorForMonth($doctorId);
        return $response;
    }

    public function destroy($id): JsonResponse
    {
        $ids = explode(',', $id);
        DoctorAvailability::whereIn('doctor_schedule_id', $ids)->delete();
        return $this->traitDestroy($id);
    }
}
