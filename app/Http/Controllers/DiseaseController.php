<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Resources\DiseaseResource;
use App\Models\Disease;
use App\Http\Requests\StoreDiseaseRequest;
use App\Http\Requests\UpdateDiseaseRequest;

class DiseaseController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = new Disease();
        $this->storeRequest = new StoreDiseaseRequest();
        $this->updateRequest = new UpdateDiseaseRequest();
        $this->resource = DiseaseResource::class;
    }
}
