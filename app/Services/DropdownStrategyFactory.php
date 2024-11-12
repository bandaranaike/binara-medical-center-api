<?php

namespace App\Services;

use InvalidArgumentException;

class DropdownStrategyFactory
{
    public function make(string $type): DropdownStrategyInterface
    {
        return match ($type) {
            'allergies' => new AllergiesDropdownStrategy(),
            'diseases' => new DiseasesDropdownStrategy(),
            'doctors' => new DoctorDropdownStrategy(),
            'hospitals' => new HospitalDropdownStrategy(),
            'patients' => new PatientDropdownStrategy(),
            'specialties' => new SpecialtyDropdownStrategy(),
            default => throw new InvalidArgumentException("Invalid type: {$type}")
        };
    }
}
