<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function billItems()
    {
        return $this->hasMany(BillItem::class);
    }
}
