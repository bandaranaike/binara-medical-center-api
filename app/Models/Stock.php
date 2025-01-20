<?php

namespace App\Models;

use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    /** @use HasFactory<StockFactory> */
    use HasFactory;

    protected $fillable = ['brand_id', 'supplier_id', 'unit_price', 'batch_number', 'quantity', 'expire_date', 'cost'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
