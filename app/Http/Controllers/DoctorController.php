<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Http\Resources\DoctorResource;
use App\Models\DoctorAvailability;
use Illuminate\Http\JsonResponse;


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

    public function destroy($id): JsonResponse
    {
        $ids = explode(',', $id);
        DoctorAvailability::whereIn('doctor_id', $ids)->delete();
        $this->model::whereIn('id', $ids)->delete();
        return new JsonResponse(['message' => 'Record deleted successfully']);
    }
}
