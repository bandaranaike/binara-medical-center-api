<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreBillCrudRequest;
use App\Http\Requests\UpdateBillCrudRequest;
use App\Http\Resources\BillCrudResource;
use App\Models\Bill;

class BillCrudController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = new Bill();
        $this->updateRequest = new UpdateBillCrudRequest();
        $this->storeRequest = new StoreBillCrudRequest();
        $this->relationships = ['patient:id,name', 'doctor:id,name'];
        $this->resource = BillCrudResource::class;
    }


}
