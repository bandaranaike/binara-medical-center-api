<?php

namespace App\Http\Controllers\Traits;

use App\Enums\AppointmentType;
use App\Enums\ServiceKey;
use App\Models\Doctor;
use App\Models\Service;

trait ServiceType
{
    private function getService($serviceType)
    {
        $serviceKey = match ($serviceType) {
            AppointmentType::DENTAL->value => ServiceKey::DENTAL_REGISTRATION->value,
            AppointmentType::OPD->value => ServiceKey::DEFAULT_DOCTOR->value,
            AppointmentType::SPECIALIST->value => ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value,
            default => null,
        };
        return Service::where('key', $serviceKey)->first();
    }
}
