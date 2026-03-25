<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static where(string $string, array|int|string $referer)
 */
class TrustedSite extends Model
{
    protected $fillable = [
        'domain',
        'api_key',
    ];

    public function publicAppTokens(): HasMany
    {
        return $this->hasMany(PublicAppToken::class);
    }
}
