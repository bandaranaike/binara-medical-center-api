<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hospital_id',
        'specialty_id',
        'telephone',
        'email',
        'age',
        'address',
    ];
}
