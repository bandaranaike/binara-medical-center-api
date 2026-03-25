<?php

namespace App\Http\Controllers\Traits;

use App\Models\Bill;
use Exception;

trait BillTrait
{
    /**
     * @throws Exception
     */
    private function hasPatientHasBook($date, $patientId, $doctorId): void
    {
        $bill = Bill::where('date', $date)->where("patient_id", $patientId)->where("doctor_id", $doctorId)->first();
        if ($bill) {
            throw new Exception("This appointment is already booked.");
        }
    }
}
