<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Stock;

class StockController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new Stock();
        $this->updateRequest = new UpdateStockRequest();
        $this->storeRequest = new StoreStockRequest();
    }
}
