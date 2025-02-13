<?php

namespace App\Enums;

enum AppointmentType: string
{
    case DENTAL = 'dental';
    case OPD = 'opd';
    case SPECIALIST = "specialist";
    case TREATMENT = 'treatment';


    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
