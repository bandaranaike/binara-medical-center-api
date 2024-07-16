<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'service_id',
        'system_amount',
        'bill_amount'
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
