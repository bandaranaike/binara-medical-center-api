<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new Supplier();
        $this->updateRequest = new UpdateSupplierRequest();
        $this->storeRequest = new StoreSupplierRequest();
    }
}
