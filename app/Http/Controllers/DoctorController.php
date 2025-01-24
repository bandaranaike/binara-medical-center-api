<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Http\Resources\DoctorResource;


class DoctorController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = new Doctor();
        $this->updateRequest = new UpdateDoctorRequest();
        $this->storeRequest = new StoreDoctorRequest();
        $this->resource = DoctorResource::class;
        $this->relationships = ['hospital:id,name', 'specialty:id,name', 'user:id,name'];
    }
}
