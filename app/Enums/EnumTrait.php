<?php

namespace App\Enums;
/**
 * @method static cases()
 */
trait EnumTrait
{
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
