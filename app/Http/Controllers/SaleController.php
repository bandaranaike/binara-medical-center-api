<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Controllers\Traits\StockTrait;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleQuantityRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Brand;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->model = new Sale();
        $this->storeRequest = new StoreSaleRequest();
        $this->updateRequest = new UpdateSaleRequest();
        $this->resource = SaleResource::class;
        $this->relationships = ['brand:id,name,drug_id', 'brand.drug:id,name'];
    }

    use CrudTrait, StockTrait;

    public function getDrugSalesForBill($billId): JsonResponse
    {
        $sales = Sale::where('bill_id', $billId)
            ->with('brand', function ($query) {
                $query->select(['id', 'name', 'drug_id'])->with('drug:id,name');
            })
            ->get(['id', 'bill_id', 'brand_id', 'quantity', 'total_price']);

        return new JsonResponse(SaleResource::collection($sales));
    }

    public function changeStockQuantity(UpdateSaleQuantityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->updateStockItemQuantity($validated['sale_id'], $validated['quantity']);

        return new JsonResponse(['message' => 'Stock quantity updated.']);
    }
}
