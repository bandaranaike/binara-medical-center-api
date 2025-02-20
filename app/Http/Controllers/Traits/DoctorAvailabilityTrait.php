<?php

namespace App\Http\Controllers\Traits;

use App\Models\DoctorAvailability;
use Exception;

trait DoctorAvailabilityTrait
{
    /**
     * @throws Exception
     */
    private function adjustDoctorSeats($doctorId, $date, $count = 1)
    {
        $doctorAvailabilities = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->first();

        if ($doctorAvailabilities->seats > 0 && $doctorAvailabilities->available_seats < $count) {
            throw new Exception("We're sorry, this doctor's schedule is full for the selected date. Please try another date.");
        }

        $doctorAvailabilities->available_seats = $doctorAvailabilities->available_seats - $count;
        return $doctorAvailabilities->save();
    }
}
