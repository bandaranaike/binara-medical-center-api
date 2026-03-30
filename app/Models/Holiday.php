<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'message',
        'is_closed',
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];
}
