<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;

class ServiceController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = new Service();
        $this->storeRequest = new StoreServiceRequest();
        $this->updateRequest = new UpdateServiceRequest();
        $this->resource = ServiceResource::class;
    }
}
