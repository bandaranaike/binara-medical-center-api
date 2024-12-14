<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property float $fee
 * @method static create(array $all)
 */
class DoctorsChannelingFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'fee',
    ];
}
