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
 */
class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'service_id',
        'system_amount',
        'bill_amount'
    ];

    /**
     * @return BelongsTo
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
