<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property mixed $role
 * @property mixed $phone
 * @property mixed $name
 * @property mixed $id
 * @property mixed $email
 * @property mixed $uuid
 * @method static create(array $array)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($user) {
            $user->uuid = (string)Str::uuid();
        });
    }

    public function hasRole(string|array $role): bool
    {
        if (!$this->role) {
            return false;
        }

        $roles = is_array($role) ? $role : [$role];

        return in_array($this->role->key, $roles);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
