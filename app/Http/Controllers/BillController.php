<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bills = Bill::all();
        return BillResource::collection($bills);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillRequest $request)
    {
        DB::transaction(function() use ($request) {
            $bill = Bill::create($request->validated());

            $billItems = collect($request->bill_items)->map(function($billItem) use ($bill) {
                return [
                    'bill_id' => $bill->id,
                    'service_id' => $billItem['service_id'],
                    'system_amount' => $billItem['system_amount'],
                    'bill_amount' => $billItem['bill_amount'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            });

            BillItem::insert($billItems->toArray());

            return new BillResource($bill->load('billItems'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        return new BillResource($bill->load('billItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        $bill->update($request->only('status'));

        return new BillResource($bill->load('billItems'));
    }

}
