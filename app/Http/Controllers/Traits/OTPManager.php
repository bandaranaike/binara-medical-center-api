<?php

namespace App\Http\Controllers\Traits;

use App\Events\SendPhoneVerification;
use App\Models\PhoneVerification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Random\RandomException;

trait OTPManager
{

    const RESEND_WAITING_SECONDS = 30;
    const TOKEN_EXPIRE_IN_MINUTES = 10;
    const SENDER = "BinaraMedic";

    private function sendOTP($phoneNumber, $otp): void
    {
        SendPhoneVerification::dispatch($phoneNumber, $otp, self::SENDER);
    }

    /**
     * @throws RandomException
     */
    private function createOTP($phoneNumber, $token = null, $userId = null): array
    {

        $otp = random_int(100000, 999999);
        $token ??= Str::random(40);
        $expiresAt = Carbon::now()->addMinutes(self::TOKEN_EXPIRE_IN_MINUTES); // OTP expires in 10 minutes

        PhoneVerification::updateOrInsert(
            ['phone_number' => $phoneNumber],
            [
                'otp' => $otp,
                'token' => $token,
                'user_id' => $userId,
                'verified_at' => null,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // $this->sendOTP($phoneNumber, $otp);

        return [$otp, $token];
    }

    /**
     * @throws Exception
     */
    private function checkPhoneHasVerified($phoneNumber): void
    {
        $isValid = PhoneVerification::where("phone_number", $phoneNumber)->whereNotNull("verified_at")->exists();
        if (!$isValid) throw new Exception("This Phone Number is not verified");
    }
}
