<?php

namespace App\Enums;

enum BillStatus: string
{
    case BOOKED = 'booked';
    case DOCTOR = 'doctor';
    case DONE = 'done';
    case PHARMACY = 'pharmacy';
    case RECEPTION = 'reception';
    case TREATMENT = 'treatment';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
