<?php

namespace App\Enums;

enum BookingTimeFilter: string
{
    case TODAY = 'today';
    case FUTURE = 'future';
    case OLD = 'old';

    public static function tryFromOrDefault(?string $value): self
    {
        return self::tryFrom($value) ?? self::TODAY;
    }
}
