<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static upsert(array[] $roles, string[] $array, string[] $array1)
 * @method static where(string $string, string $ROLE_DOCTOR)
 */
class Role extends Model
{



    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected $fillable = ["name", "key", "description"];
}
