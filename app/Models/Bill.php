<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bill extends Model
{
    use HasFactory;

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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function patientMedicineBillItem(): HasOne
    {
        return $this->hasOne(BillItem::class)->where('service_id', '=', 3);
    }
}
