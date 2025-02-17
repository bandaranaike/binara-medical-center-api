<?php

namespace App\Enums;

enum PaymentType: string
{
    use EnumTrait;

    case CASH = 'cash';
    case CARD = 'card';
    case ONLINE = 'online';
}
