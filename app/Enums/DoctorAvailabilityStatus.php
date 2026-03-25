<?php

namespace App\Enums;

enum DoctorAvailabilityStatus: string
{
    use EnumTrait;

    case ACTIVE = 'active';
    case CANCELED = 'canceled';
}
