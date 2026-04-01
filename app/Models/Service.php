<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static getByKey(mixed $get)
 * @method static create(array $array)
 * @method static where(string $string, string $MEDICINE_KEY)
 * @method static upsert(array[] $services, string $string)
 */
class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'bill_price',
        'system_price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bill_price' => 'decimal:2',
            'system_price' => 'decimal:2',
            'separate_items' => 'boolean',
            'is_percentage' => 'boolean',
        ];
    }

    public function scopeGetByKey($query, $key)
    {
        return $query->where('key', $key);
    }
}
