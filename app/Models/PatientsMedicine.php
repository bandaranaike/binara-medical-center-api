<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientsMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_item_id',
        'dosage',
        'medicine_id',
        'type',
        'duration',
        'quantity',
        'price',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
