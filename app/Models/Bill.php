<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(mixed $validated)
 * @method static where(string $string, string $STATUS_PENDING)
 * @method static whereStatus(string $STATUS_PENDING)
 */
class Bill extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_BOOKED = 'booked';
    public const STATUS_DOCTOR = 'doctor';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PHARMACY = 'pharmacy';
    public const STATUS_TEMPORARY = 'temporary';

    protected $fillable = [
        'system_amount',
        'bill_amount',
        'patient_id',
        'doctor_id',
        'status'
    ];

    public function billItems(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function dailyPatientQueue(): HasOne
    {
        return $this->hasOne(DailyPatientQueue::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
