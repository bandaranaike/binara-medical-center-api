<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static findOrFail(mixed $request)
 */
class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hospital_id',
        'specialty_id',
        'telephone',
        'email',
        'age',
        'address',
        'doctor_type',
        'user_id',
    ];

    public function channellingFee(): HasOne
    {
        return $this->hasOne(DoctorsChannelingFee::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctorAvailabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class);
    }
}
