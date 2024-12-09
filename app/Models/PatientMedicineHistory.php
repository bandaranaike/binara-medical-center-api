<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 * @method static where(string $string, $patientId)
 */
class PatientMedicineHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medicine_id',
        'doctor_id',
        'bill_id',
        'dosage',
        'type',
        'duration'
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
