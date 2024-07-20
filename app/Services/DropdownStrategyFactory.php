<?php

namespace App\Services;

use InvalidArgumentException;

class DropdownStrategyFactory
{
    public function make(string $type): DropdownStrategyInterface
    {
        return match ($type) {
            'doctors' => new DoctorDropdownStrategy(),
            'patients' => new PatientDropdownStrategy(),
            default => throw new InvalidArgumentException("Invalid type: {$type}")
        };
    }
}
