<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property numeric $id
 */
class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'address',
        'telephone',
        'email',
        'birthday',
        'gender'
    ];

    /**
     * Set the patient's birthday.
     *
     * @param string|null $value
     * @return void
     */
    public function setBirthdayAttribute(string|null $value): void
    {
        $this->attributes['birthday'] = Carbon::parse($value)->format('Y-m-d');
    }

    /**
     * Get the patient's birthday.
     *
     * @param string|null $value
     * @return string
     */
    public function getBirthdayAttribute(string|null $value): string
    {
        return Carbon::parse($value)->toDateString();
    }
}
