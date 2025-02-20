<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Http\Resources\DoctorScheduleResource;
use App\Models\DoctorSchedule;

class DoctorScheduleController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new DoctorSchedule();
        $this->updateRequest = new UpdateDoctorScheduleRequest();
        $this->storeRequest = new StoreDoctorScheduleRequest();
        $this->resource = DoctorScheduleResource::class;
        $this->relationships = ['doctor:id,name'];
    }
}
