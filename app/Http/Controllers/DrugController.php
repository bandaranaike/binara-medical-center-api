<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDrugRequest;
use App\Http\Requests\UpdateDrugRequest;
use App\Models\Drug;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DrugController extends Controller
{

    use CrudTrait;

    public function __construct()
    {
        $this->model = Drug::class;
        $this->storeRequest = StoreDrugRequest::class;
        $this->updateRequest = UpdateDrugRequest::class;
    }

    public function getDrugStockSaleData(): JsonResponse
    {
        $data = Drug::with(['brands' => function ($query) {
            $query->withCount([
                'stocks as stock_quantity' => function ($q) {
                    $q->select(DB::raw('SUM(quantity)'));
                },
                'sales as sale_quantity' => function ($q) {
                    $q->select(DB::raw('SUM(quantity)'));
                }
            ])->with('stocks:id,brand_id,unit_price,cost,expire_date');
        }])->get();

        $formattedData = $data->flatMap(function ($drug) {
            return $drug->brands->map(function ($brand) use ($drug) {
                $stock = $brand->stocks->first(); // Assuming one stock record per brand
                return [
                    'id' => $drug->id,
                    'drug_name' => $drug->name,
                    'brand_name' => $brand->name,
                    'stock_quantity' => $brand->stock_quantity ?? 0,
                    'sale_quantity' => $brand->sale_quantity ?? 0,
                    'unit_price' => $stock->unit_price ?? 0,
                    'cost' => $stock->cost ?? 0,
                    'expire_date' => $stock->expire_date ?? 0,
                ];
            });
        });

        return response()->json($formattedData);
    }
}
