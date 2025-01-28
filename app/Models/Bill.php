<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @method static create(mixed $validated)
 * @method static where(string $string, string $operator, string $STATUS_PENDING = null)
 * @method static whereStatus(string $STATUS_PENDING)
 * @method static selectRaw(string $string)
 * @method static firstOrCreate(array $array, array $array1)
 */
class Bill extends Model
{
    use HasFactory, SoftDeletes;

    public const FEE_ORIGINAL = 'fee';
    public const FEE_INSTITUTION = 'institution fee';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_DOCTOR = 'doctor';
    public const STATUS_DONE = 'done';
    public const STATUS_PHARMACY = 'pharmacy';
    public const STATUS_RECEPTION = 'reception';
    public const STATUS_TREATMENT = 'treatment';

    protected $fillable = [
        'system_amount',
        'bill_amount',
        'patient_id',
        'doctor_id',
        'status'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($bill) {
            $bill->uuid = (string)Str::uuid();
        });
    }

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

    public function patientMedicines(): HasMany
    {
        return $this->hasMany(PatientMedicineHistory::class);
    }

}
