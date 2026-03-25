<?php

namespace App\Enums;

enum DoctorScheduleStatus: string
{
    use EnumTrait;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
