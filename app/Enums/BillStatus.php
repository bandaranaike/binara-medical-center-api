<?php

namespace App\Enums;

enum BillStatus: string
{
    use EnumTrait;

    case BOOKED = 'booked';
    case DOCTOR = 'doctor';
    case DONE = 'done';
    case PHARMACY = 'pharmacy';
    case RECEPTION = 'reception';
    case TREATMENT = 'treatment';

}
