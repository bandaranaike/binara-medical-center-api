<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property numeric $id
 *
 * @method static find(int $patientId)
 * @method static select(string...$string)
 */
class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'age',
        'address',
        'telephone',
        'email',
        'registration_no',
        'birthday',
        'gender',
        'user_id',
    ];

    public function allergies(): BelongsToMany
    {
        return $this->belongsToMany(Allergy::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class);
    }

    public function patientHistories(): hasMany
    {
        return $this->hasMany(PatientsHistory::class);
    }

    /**
     * Set the patient's birthday.
     */
    public function setBirthdayAttribute(?string $value): void
    {
        $this->attributes['birthday'] = Carbon::parse($value)->format('Y-m-d');
    }
}
