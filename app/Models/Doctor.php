<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    public function channellingFee(): HasOne
    {
        return $this->hasOne(DoctorsChannelingFee::class);
    }
}
