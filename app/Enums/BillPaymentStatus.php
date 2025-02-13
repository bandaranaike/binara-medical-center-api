<?php

namespace App\Enums;

enum BillPaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    case REFUNDED = 'refunded';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
