<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Doctor extends Model
{
    use HasFactory;

    const DOCTOR_TYPE_DENTAL = "dental";
    const DOCTOR_TYPE_OPD = "opd";
    const DOCTOR_TYPE_SPECIALIST = "specialist";

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
}
