<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static upsert(array[] $roles, string[] $array, string[] $array1)
 */
class Role extends Model
{

    const ROLE_ADMIN = 'admin';
    const ROLE_PATIENT = 'patient';
    const ROLE_PHARMACY = 'pharmacy';
    const ROLE_DOCTOR = 'doctor';
    const ROLE_NURSE = 'nurse';
    const ROLE_RECEPTION = 'reception';
    const ROLE_PHARMACY_ADMIN = 'pharmacy_admin';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
