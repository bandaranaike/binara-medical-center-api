<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contact extends Model
{

    protected $fillable = ['name', 'email', 'phone', 'message', 'reference'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($bill) {
            $bill->reference = Str::random(4);
        });
    }
}
