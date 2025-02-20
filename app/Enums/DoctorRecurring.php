<?php

namespace App\Enums;

enum DoctorRecurring: string

{
    use EnumTrait;

    case DAILY = 'Daily';
    case WEEKLY = 'Weekly';
    case BI_WEEKLY = 'Bi-Weekly';
    case MONTHLY = 'Monthly';
    case BI_MONTHLY = 'Bi-Monthly';
    case QUARTERLY = 'Quarterly';
    case YEARLY = 'Yearly';
    case ONCE = 'Once';
    case AS_NEEDED = 'As Needed';
    case VARIABLE = 'Variable';
}
