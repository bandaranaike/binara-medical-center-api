<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property numeric $id
 * @method static find(int $patientId)
 * @method static select(string...$string)
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
        'gender',
        'user_id'
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
