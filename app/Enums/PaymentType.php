<?php

namespace App\Enums;

enum PaymentType: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case ONLINE = 'online';


    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
