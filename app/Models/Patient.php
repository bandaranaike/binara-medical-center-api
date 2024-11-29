<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @return BelongsToMany
     */
    public function allergies(): BelongsToMany
    {
        return $this->belongsToMany(Allergy::class);
    }

    /**
     * @return HasMany
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * @return BelongsToMany
     */
    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class);
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

    /**
     * @return HasMany
     */
    public function patientHistories(): hasMany
    {
        return $this->hasMany(PatientsHistory::class);
    }

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
}
