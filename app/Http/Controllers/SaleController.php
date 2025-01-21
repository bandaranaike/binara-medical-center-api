<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->model = new Sale();
        $this->storeRequest = new StoreSaleRequest();
        $this->updateRequest = new UpdateSaleRequest();
    }

    use CrudTrait;
}
