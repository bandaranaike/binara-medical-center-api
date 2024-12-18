<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function convertToBill(Request $request)
    {
        return new JsonResponse(Bill::where('id', $request->get('bill_id'))->update([
            'status' => Bill::STATUS_DOCTOR
        ]));
    }
}
