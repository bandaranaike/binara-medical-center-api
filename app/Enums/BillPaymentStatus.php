<?php

namespace App\Enums;

enum BillPaymentStatus: string
{
    use EnumTrait;

    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    case REFUNDED = 'refunded';

}
