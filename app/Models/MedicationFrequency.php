<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class MedicationFrequency extends Model
{
    protected $fillable = ['name'];

    public $timestamps = false;
}
