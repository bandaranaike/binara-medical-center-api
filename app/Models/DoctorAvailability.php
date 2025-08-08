<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static whereIn(string $string, string[] $ids)
 */
class DoctorAvailability extends Model
{
    protected $fillable = ['doctor_id', 'date', 'time', 'seats', 'available_seats', 'status'];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
