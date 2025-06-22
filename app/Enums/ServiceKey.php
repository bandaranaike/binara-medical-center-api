<?php

namespace App\Enums;

/**
 * @method static upsert(array[] $services, string $string)
 */
enum ServiceKey: string
{
    case DEFAULT_SPECIALIST_CHANNELING = 'channeling';
    case DEFAULT_DOCTOR = 'opd-doctor';
    case MEDICINE = 'opd-medicine';
    case DENTAL_REGISTRATION = 'dental-registration';
    case DENTAL_TREATMENTS = 'dental-treatments';
    case DENTAL_LAB = 'dental-lab';
    case WOUND_DRESSING = 'wound-dressing';
}
