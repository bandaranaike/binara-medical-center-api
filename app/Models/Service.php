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

    const DEFAULT_SPECIALIST_CHANNELING_KEY = 'channeling';
    const DEFAULT_DOCTOR_KEY = 'opd-doctor';
    const MEDICINE_KEY = 'medicine';
    const DENTAL_REGISTRATION_KEY = 'dental-registration';
    const DENTAL_TREATMENTS_KEY = 'dental-treatments';
    const DENTAL_LAB_KEY = 'dental-lab';
    const WOUND_DRESSING_KEY = "wound-dressing";

    protected $fillable = [
        'name',
        'key',
        'bill_price',
        'system_price',
    ];

    public function scopeGetByKey($query, $key)
    {
        return $query->where('key', $key);
    }
}
