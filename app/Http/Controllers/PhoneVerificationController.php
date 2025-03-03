<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\OTPManager;
use App\Http\Requests\SendPhoneVerificationRequest;
use App\Http\Requests\ResendPhoneVerificationRequest;
use App\Http\Requests\ValidatePhoneVerificationRequest;
use App\Models\PhoneVerification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Random\RandomException;

class PhoneVerificationController extends Controller
{

    use OTPManager;

    public function resend(ResendPhoneVerificationRequest $request, $token): JsonResponse
    {
        // Get the verification record using the token
        $phoneVerification = PhoneVerification::where('token', $token)->first();
        if (!$phoneVerification) {
            return new JsonResponse("Token not found", 404);
        }

        $lastSent = Carbon::parse($phoneVerification->updated_at);
        $now = Carbon::now();

        // If the resend is too soon, user need to wait
        if ($lastSent->diffInSeconds($now) < self::RESEND_WAITING_SECONDS) {
            return new JsonResponse(['message' => 'Please wait before resending'], 429); // 429 Too Many Requests
        }

        // If the existing record has not expired, can be updated and resend it
        if (Carbon::now()->lessThan($phoneVerification->expires_at)) {
            $this->sendOTP($phoneVerification->phone_number, $phoneVerification->otp);
            $phoneVerification->update(['updated_at' => Carbon::now()]);
            return new JsonResponse(['message' => 'OTP resend timer updated']);
        }

        // Generating a new OTP for the same token
        try {
            [$otp] = $this->createOTP($phoneVerification->phone_number, $phoneVerification->token);
        } catch (RandomException $e) {
            return new JsonResponse(['message' => 'OTP generate failed. ' . $e->getMessage()], 500);
        }

        return new JsonResponse(['message' => 'New OTP sent successfully. Testing:' . $otp]);
    }

    public function validate(ValidatePhoneVerificationRequest $request, $token): JsonResponse
    {

        $otp = $request->validated('otp');

        $verification = PhoneVerification::where('token', $token)
            ->where('otp', $otp)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$verification) {
            return new JsonResponse(['message' => 'The OTP did not match. Please ensure you\'ve entered it correctly'], 400);
        }

        $verification->update(['verified_at' => Carbon::now()]);


        return new JsonResponse(['message' => 'Phone number verified successfully']);

    }

    public function request(SendPhoneVerificationRequest $request)
    {
        $phoneNumber = $request->validated('phone_number');

        try {
            [$otp, $token] = $this->createOTP($phoneNumber);
        } catch (RandomException $e) {
            return new JsonResponse(['message' => 'OTP generate failed. ' . $e->getMessage()], 500);
        }
        return new JsonResponse(['message' => 'OTP generated successfully', 'otp' => $otp, 'token' => $token]);
    }
}
