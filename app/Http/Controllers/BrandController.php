<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;

class BrandController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = new Brand();
        $this->updateRequest = new UpdateBrandRequest();
        $this->storeRequest = new StoreBrandRequest();
        $this->relationships = ['drug:id,name'];
        $this->resource = BrandResource::class;
    }
}
