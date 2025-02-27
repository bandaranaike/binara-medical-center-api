<?php

namespace App\Http\Controllers\Traits;

use App\Models\DailyPatientQueue;

trait DailyPatientQueueTrait
{

    private function createDailyPatientQueue($billId, $doctorId, $date = null): int
    {
        $date = $date ?? date('Y-m-d');

        $latestRecord = DailyPatientQueue::where('doctor_id', $doctorId)->where('queue_date', $date)->orderByDesc('id')->first();

        $newRecord = new DailyPatientQueue();
        $newRecord->bill_id = $billId;
        $newRecord->doctor_id = $doctorId;
        $newRecord->queue_date = $date;
        $newRecord->queue_number = $latestRecord ? $latestRecord->queue_number + 1 : 1;
        $newRecord->order_number = $latestRecord ? $latestRecord->order_number + 1 : 1;
        $newRecord->save();

        return $newRecord->queue_number;

    }
}
