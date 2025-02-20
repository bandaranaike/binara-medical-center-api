<?php

namespace App\Enums;

enum AppointmentType: string
{
    use EnumTrait;

    case DENTAL = 'dental';
    case OPD = 'opd';
    case SPECIALIST = "specialist";
    case TREATMENT = 'treatment';
}
