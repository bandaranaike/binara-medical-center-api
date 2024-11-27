<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
