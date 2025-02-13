<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, $billId)
 */
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    protected $fillable = ['bill_id', 'brand_id', 'quantity', 'total_price'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}
