<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrInsert(array $array, array $array1)
 * @method static where(string $string, $phoneNumber)
 */
class PhoneVerification extends Model
{
    protected $fillable = ['phone_number', 'otp', 'token', 'user_id', 'verified_at', 'expires_at', 'created_at', 'updated_at'];
}
