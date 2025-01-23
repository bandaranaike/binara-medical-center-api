<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Http\Resources\SpecialtyResource;
use App\Models\Specialty;
use Illuminate\Http\JsonResponse;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use CrudTrait;

    public function __construct()
    {
        $this->model = new Specialty();
        $this->storeRequest = new StoreSpecialtyRequest();
        $this->updateRequest = new UpdateSpecialtyRequest();
        $this->resource = SpecialtyResource::class;
    }
}
