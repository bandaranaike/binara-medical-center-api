<?php

namespace App\Http\Controllers\Traits;

use App\Enums\AppointmentType;
use App\Models\Doctor;
use App\Models\Service;

trait ServiceType
{
    private function getService($serviceType)
    {
        $serviceKey = match ($serviceType) {
            AppointmentType::DENTAL => Service::DENTAL_REGISTRATION_KEY,
            AppointmentType::OPD => Service::DEFAULT_DOCTOR_KEY,
            AppointmentType::SPECIALIST => Service::DEFAULT_SPECIALIST_CHANNELING_KEY,
            default => null,
        };

        return Service::where('key', $serviceKey)->first();
    }
}
