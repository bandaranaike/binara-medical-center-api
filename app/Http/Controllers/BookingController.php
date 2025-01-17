<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\PrintingDataProcess;
use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use PrintingDataProcess;

    public function convertToBill(Request $request): JsonResponse
    {
        $bill = Bill::where('id', $request->get('bill_id'))
            ->with('patient:id,name')
            ->with('doctor:id,name')
            ->first();
        $billItems = $this->getBillItemsFroPrint($bill->id);

        $bill->status = Bill::STATUS_DOCTOR;
        $bill->save();

        return new JsonResponse([
            'patient_name' => $bill->patient->name,
            'doctor_name' => $bill->doctor?->name,
            "bill_items" => $billItems,
            'total' => $bill->bill_amount + $bill->system_amount
        ]);
    }
}
