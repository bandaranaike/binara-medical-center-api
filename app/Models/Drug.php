<?php

namespace App\Models;

use Database\Factories\DrugFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drug extends Model
{
    /** @use HasFactory<DrugFactory> */
    use HasFactory;

    protected $fillable = ['name', 'minimum_quantity', 'category_id'];

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
