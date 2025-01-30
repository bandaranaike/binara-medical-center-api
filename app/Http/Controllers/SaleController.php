<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreSaleRequest;
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

    use CrudTrait;

    public function store(StoreSaleRequest $request): JsonResponse
    {

        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'quantity' => 'required|integer|min:1',
            'bill_id' => 'required|exists:bills,id'
        ]);

        return DB::transaction(function () use ($validated) {
            $brand = Brand::findOrFail($validated['brand_id']);
            $quantityToDeduct = $validated['quantity'];
            $totalPrice = 0;

            // Get stocks ordered by oldest first (based on expire_date)
            $stocks = Stock::where('brand_id', $brand->id)
                ->where('quantity', '>', 0)
                ->orderBy('expire_date', 'asc')
                ->get();

            if ($stocks->sum('quantity') < $quantityToDeduct) {
                abort(422, 'Insufficient stock for this brand');
            }

            $updatedStocks = [];
            foreach ($stocks as $stock) {
                $deductible = min($quantityToDeduct, $stock->quantity);

                $stock->quantity -= $deductible;
                $totalPrice += $deductible * $stock->unit_price;
                $quantityToDeduct -= $deductible;

                $updatedStocks[] = [
                    'stock' => $stock,
                    'deducted_quantity' => $deductible
                ];

                if ($quantityToDeduct === 0) {
                    break;
                }
            }

            // Create the sale record
            $sale = Sale::create([
                'brand_id' => $brand->id,
                'bill_id' => $validated['bill_id'],
                'quantity' => $validated['quantity'],
                'total_price' => $totalPrice,
            ]);

            // Save all stock updates
            foreach ($updatedStocks as $updatedStock) {
                $updatedStock['stock']->save();
            }

            return response()->json([
                'message' => 'Sale completed successfully',
                'sale' => $sale,
                'stocks_updated' => collect($updatedStocks)->map(function ($item) {
                    return [
                        'stock_id' => $item['stock']->id,
                        'deducted_quantity' => $item['deducted_quantity'],
                        'remaining_quantity' => $item['stock']->quantity
                    ];
                })
            ], 201);
        });
    }

    public function destroy($id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $sale = Sale::find($id);
            $brand = $sale->brand;
            $quantityToRestore = $sale->quantity;

            // Get stocks that belong to this brand, ordered by newest first (LIFO restoration)
            $stocks = Stock::where('brand_id', $brand->id)
                ->orderBy('updated_at', 'desc') // Assuming updated_at tracks stock changes
                ->get();

            $restoredStocks = [];
            foreach ($stocks as $stock) {
                $restorable = min($quantityToRestore, $stock->initial_quantity - $stock->quantity);

                if ($restorable > 0) {
                    $stock->quantity += $restorable;
                    $quantityToRestore -= $restorable;

                    $restoredStocks[] = [
                        'stock' => $stock,
                        'restored_quantity' => $restorable
                    ];

                    if ($quantityToRestore === 0) {
                        break;
                    }
                }
            }

            if ($quantityToRestore > 0) {
                abort(422, 'Error restoring stock. Some quantities may be missing.');
            }

            // Delete the sale record
            $sale->delete();

            // Save all stock updates
            foreach ($restoredStocks as $restoredStock) {
                $restoredStock['stock']->save();
            }

            return response()->json([
                'message' => 'Sale reversed successfully',
                'sale_id' => $sale->id,
                'stocks_restored' => collect($restoredStocks)->map(function ($item) {
                    return [
                        'stock_id' => $item['stock']->id,
                        'restored_quantity' => $item['restored_quantity'],
                        'current_quantity' => $item['stock']->quantity
                    ];
                })
            ], 200);
        });
    }


    public function getDrugSalesForBill($billId): JsonResponse
    {
        $sales = Sale::where('bill_id', $billId)
            ->with('brand', function ($query) {
                $query->select(['id', 'name', 'drug_id'])->with('drug:id,name');
            })
            ->get(['id', 'bill_id', 'brand_id', 'quantity', 'total_price']);

        return new JsonResponse(SaleResource::collection($sales));
    }
}
