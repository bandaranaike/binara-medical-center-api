<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @method static where(string $string, string $string1, string $toDateString)
 */
class DailyPatientQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'doctor_id',
        'queue_date',
        'queue_number',
        'order_number'
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
