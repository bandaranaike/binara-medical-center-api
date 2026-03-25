<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PublicAppToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'trusted_site_id',
        'name',
        'token_hash',
        'abilities',
        'last_used_at',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function trustedSite(): BelongsTo
    {
        return $this->belongsTo(TrustedSite::class);
    }

    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? ['*'];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public static function issueForTrustedSite(
        TrustedSite $trustedSite,
        string $name,
        array $abilities = ['*'],
        mixed $expiresAt = null,
    ): array {
        $plainTextToken = 'pta_'.Str::random(64);

        $token = static::create([
            'trusted_site_id' => $trustedSite->id,
            'name' => $name,
            'token_hash' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return [$token, $plainTextToken];
    }
}
