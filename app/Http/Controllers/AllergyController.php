<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Resources\AllergyResource;
use App\Models\Allergy;
use App\Http\Requests\StoreAllergyRequest;
use App\Http\Requests\UpdateAllergyRequest;

class AllergyController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new Allergy();
        $this->storeRequest = new StoreAllergyRequest();
        $this->updateRequest = new UpdateAllergyRequest();
        $this->resource = AllergyResource::class;
    }
}
