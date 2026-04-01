<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static firstOrCreate(array $array)
 * @method static findOrFail($id)
 * @method static insert(array[] $data)
 * @method static where(string $string, $id)
 * @method static create(array $array)
 */
class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'service_id',
        'service_name',
        'service_key',
        'doctor_id',
        'system_amount',
        'bill_amount',
        'referred_amount',
        'category',
        'is_ad_hoc',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bill_amount' => 'decimal:2',
            'system_amount' => 'decimal:2',
            'referred_amount' => 'decimal:2',
            'is_ad_hoc' => 'boolean',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
