<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'ticket_number',
        'date',
    ];

    // Define relationship to Doctor model if you have it
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
