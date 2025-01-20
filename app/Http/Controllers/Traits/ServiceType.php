<?php

namespace App\Http\Controllers\Traits;

use App\Models\Doctor;
use App\Models\Service;

trait ServiceType
{
    private function getService($serviceType)
    {
        $serviceKey = match ($serviceType) {
            Doctor::DOCTOR_TYPE_DENTAL => Service::DENTAL_REGISTRATION_KEY,
            Doctor::DOCTOR_TYPE_OPD => Service::DEFAULT_DOCTOR_KEY,
            Doctor::DOCTOR_TYPE_SPECIALIST => Service::DEFAULT_SPECIALIST_CHANNELING_KEY,
        };

        return Service::where('key', $serviceKey)->first();
    }
}
