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
            AppointmentType::DENTAL->value => Service::DENTAL_REGISTRATION_KEY,
            AppointmentType::OPD->value => Service::DEFAULT_DOCTOR_KEY,
            AppointmentType::SPECIALIST->value => Service::DEFAULT_SPECIALIST_CHANNELING_KEY,
            default => null,
        };
        return Service::where('key', $serviceKey)->first();
    }
}
