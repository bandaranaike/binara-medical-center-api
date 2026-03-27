<?php

namespace App\Models;

use App\Enums\BillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @method static create(mixed $validated)
 * @method static where(string $string, string|BillStatus $operator, string|BillStatus $STATUS_PENDING = null)
 * @method static whereStatus(string $STATUS_PENDING)
 * @method static selectRaw(string $string)
 * @method static firstOrCreate(array $array, array $array1)
 */
class Bill extends Model
{
    use HasFactory, SoftDeletes;

    public const BILL_REGISTRATION_PREFIX = 'BILL-';

    public const BOOKING_REGISTRATION_PREFIX = 'BOOK-';

    public const FEE_ORIGINAL = 'fee';

    public const FEE_INSTITUTION = 'institution fee';

    protected $fillable = [
        'bill_registration_number',
        'booking_registration_number',
        'system_amount',
        'bill_amount',
        'patient_id',
        'doctor_id',
        'status',
        'shift',
        'payment_type',
        'payment_status',
        'appointment_type',
        'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($bill) {
            $bill->uuid = (string) Str::uuid();
        });

        static::created(function (Bill $bill): void {
            $updates = [];

            if (blank($bill->bill_registration_number)) {
                $updates['bill_registration_number'] = self::formatBillRegistrationNumber($bill->id);
            }

            if (self::hasBookedStatus($bill->status) && blank($bill->booking_registration_number)) {
                $updates['booking_registration_number'] = self::formatBookingRegistrationNumber($bill->id);
            }

            if ($updates !== []) {
                $bill->forceFill($updates)->saveQuietly();
            }
        });

        static::updating(function (Bill $bill): void {
            if (self::hasBookedStatus($bill->status) && blank($bill->booking_registration_number)) {
                $bill->booking_registration_number = self::formatBookingRegistrationNumber($bill->id);
            }
        });
    }

    public static function formatBillRegistrationNumber(int $billId): string
    {
        return self::BILL_REGISTRATION_PREFIX.str_pad((string) $billId, 6, '0', STR_PAD_LEFT);
    }

    public static function formatBookingRegistrationNumber(int $billId): string
    {
        return self::BOOKING_REGISTRATION_PREFIX.str_pad((string) $billId, 6, '0', STR_PAD_LEFT);
    }

    private static function hasBookedStatus(BillStatus|string|null $status): bool
    {
        return $status === BillStatus::BOOKED || $status === BillStatus::BOOKED->value;
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
