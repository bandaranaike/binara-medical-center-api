<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreDrugRequest;
use App\Http\Requests\UpdateDrugRequest;
use App\Http\Resources\DrugResource;
use App\Models\Drug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DrugController extends Controller
{

    use CrudTrait;

    const DRUG_LIMIT = 40;

    public function __construct()
    {
        $this->model = new Drug();
        $this->storeRequest = new StoreDrugRequest();
        $this->updateRequest = new UpdateDrugRequest();
        $this->resource = DrugResource::class;
        $this->relationships = ['category:id,name'];
    }

    public function getDrugStockSaleData(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('per_page', self::DRUG_LIMIT);
        $search = trim((string)$request->input('q', ''));

        // SUM(stocks.quantity) per brand
        $stockSums = DB::table('stocks')
            ->select('brand_id', DB::raw('SUM(quantity) AS stock_quantity'))
            ->groupBy('brand_id');

        // SUM(sales.quantity) per brand
        $saleSums = DB::table('sales')
            ->select('brand_id', DB::raw('SUM(quantity) AS sale_quantity'))
            ->groupBy('brand_id');

        // Latest stock row per brand (by max id; swap to created_at/expire_date if needed)
        $latestStockIds = DB::table('stocks')
            ->select('brand_id', DB::raw('MAX(id) AS latest_stock_id'))
            ->groupBy('brand_id');

        $latestStock = DB::table('stocks AS s')
            ->joinSub($latestStockIds, 'ls', function ($j) {
                $j->on('s.id', '=', 'ls.latest_stock_id');
            })
            ->select('ls.brand_id', 's.unit_price', 's.cost', 's.expire_date');

        $query = DB::table('brands')
            ->join('drugs', 'brands.drug_id', '=', 'drugs.id')
            ->leftJoinSub($stockSums, 'ss', fn($j) => $j->on('ss.brand_id', '=', 'brands.id'))
            ->leftJoinSub($saleSums, 'sa', fn($j) => $j->on('sa.brand_id', '=', 'brands.id'))
            ->leftJoinSub($latestStock, 'ls', fn($j) => $j->on('ls.brand_id', '=', 'brands.id'))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('drugs.name', 'like', "%{$search}%")
                        ->orWhere('brands.name', 'like', "%{$search}%");
                });
            })
            ->orderBy('drugs.name')
            ->orderBy('brands.name')
            ->select([
                'drugs.id as id',
                'drugs.name as drug_name',
                'brands.name as brand_name',
                DB::raw('COALESCE(ss.stock_quantity, 0)  AS stock_quantity'),
                DB::raw('COALESCE(sa.sale_quantity, 0)   AS sale_quantity'),
                DB::raw('COALESCE(ls.unit_price, 0)      AS unit_price'),
                DB::raw('COALESCE(ls.cost, 0)            AS cost'),
                'ls.expire_date',
                DB::raw('COALESCE(drugs.minimum_quantity, 0) AS minimum_quantity'),
            ]);

        $page = $query->paginate($perPage);

        // Return standard Laravel pagination JSON structure
        return response()->json($page);
    }
}
