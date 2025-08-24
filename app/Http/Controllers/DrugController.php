<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDrugRequest;
use App\Http\Requests\UpdateDrugRequest;
use App\Http\Resources\DrugResource;
use App\Models\Drug;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DrugController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = new Drug();
        $this->storeRequest = new StoreDrugRequest();
        $this->updateRequest = new UpdateDrugRequest();
        $this->resource = DrugResource::class;
        $this->relationships = ['category:id,name'];
    }

    public function getDrugStockSaleData(): JsonResponse
    {
        $data = Drug::with(['brands' => function ($query) {
            $query
                ->withSum('stocks as stock_quantity', 'quantity')
                ->withSum('sales as sale_quantity', 'quantity')
                ->with('stocks:id,brand_id,unit_price,cost,expire_date');
        }])->get();

        $formattedData = $data->flatMap(function ($drug) {
            return $drug->brands->map(function ($brand) use ($drug) {
                $stock = $brand->stocks->first(); // Assuming one stock record per brand
                return [
                    'id' => $drug->id,
                    'drug_name' => $drug->name,
                    'brand_name' => $brand->name,
                    'stock_quantity' => $brand->stock_quantity ?? 0,
                    'stock_quantities' => $brand->stock_quantities ?? 0,
                    'sale_quantity' => $brand->sale_quantity ?? 0,
                    'unit_price' => $stock->unit_price ?? 0,
                    'cost' => $stock->cost ?? 0,
                    'expire_date' => $stock->expire_date ?? 0,
                    'minimum_quantity' => $drug->minimum_quantity ?? 0,
                ];
            });
        });

        return new JsonResponse($formattedData);
    }
}
