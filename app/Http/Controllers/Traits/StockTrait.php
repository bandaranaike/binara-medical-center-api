<?php

namespace App\Http\Controllers\Traits;

use App\Events\PatientMedicineListUpdated;
use App\Exceptions\InsufficientStocksException;
use App\Models\PatientMedicineHistory;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\TemporarySale;
use Illuminate\Support\Facades\DB;

trait StockTrait
{
    /**
     * @param $brandId
     * @param $quantity
     * @param $billId
     * @return mixed
     * @throws InsufficientStocksException
     */
    public function addSaleItem($brandId, $quantity, $billId): mixed
    {
        return DB::transaction(function () use ($brandId, $quantity, $billId) {
            $stocks = $this->getStocksForBrand($brandId);

            if ($stocks->sum('quantity') < $quantity) {
                throw new InsufficientStocksException('Stock quantity exceeded. Max ' . $stocks->sum('quantity'));
            }

            // Since temp records required sale id, we need to create sale first
            $sale = Sale::create([
                'brand_id' => $brandId,
                'bill_id' => $billId,
                'quantity' => $quantity,
                'total_price' => 0,
            ]);

            $this->createSaleFromStocks($stocks, $quantity, $sale);

            return $sale->id;
        });
    }

    private function getStocksForBrand($brandId): mixed
    {
        return Stock::where('brand_id', $brandId)
            ->where('quantity', '>', 0)
            ->orderBy('expire_date', 'asc')
            ->get();
    }

    private function restoreStock($saleId): void
    {

        $tempSales = TemporarySale::where('sale_id', $saleId)->get();

        foreach ($tempSales as $tempSale) {
            $stock = Stock::find($tempSale->stock_id);
            $stock->quantity += $tempSale->quantity;

            $stock->save();
            $tempSale->delete();
        }

    }

    private function createSaleFromStocks($stocks, $quantity, $sale): void
    {
        $quantityToDeduct = $quantity;
        $totalPrice = 0;
        $stockTempRecords = [];

        foreach ($stocks as $stock) {
            $deductible = min($quantityToDeduct, $stock->quantity);

            $stock->quantity -= $deductible;
            $totalPrice += $deductible * $stock->unit_price;
            $quantityToDeduct -= $deductible;

            $stockTempRecords[] = ['stock_id' => $stock->id, 'quantity' => $deductible, 'sale_id' => $sale->id, 'bill_id' => $sale->bill_id];

            $stock->save();
            if ($quantityToDeduct === 0) {
                break;
            }
        }

        TemporarySale::insert($stockTempRecords);

        // Update total price
        $sale->quantity = $quantity - $quantityToDeduct;
        $sale->total_price = $totalPrice;
        $sale->save();

        event(new PatientMedicineListUpdated($sale->bill_id, $totalPrice));

    }

    public function removeSaleItem($saleId): void
    {
        $this->restoreStock($saleId);
        PatientMedicineHistory::where('sale_id', $saleId)->delete();
        Sale::findOrFail($saleId)->delete();
        TemporarySale::where('sale_id', $saleId)->delete();
    }

    public function updateStockItemQuantity($saleId, $newQuantity): void
    {
        $sale = Sale::findOrFail($saleId);
        $this->restoreStock($saleId);
        $stocks = $this->getStocksForBrand($sale->brand_id);
        $this->createSaleFromStocks($stocks, $newQuantity, $sale);
    }
}
