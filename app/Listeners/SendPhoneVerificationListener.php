<?php

namespace App\Listeners;

use App\Events\SendPhoneVerification;
use App\Services\DialogESMSService;

class SendPhoneVerificationListener
{
    /**
     * Handle the event.
     */
    public function handle(SendPhoneVerification $event): void
    {
        $esmsService = new DialogESMSService(config('services.dialog.api_key'));
        $esmsService->sendMessage($event->phone, "Your OTP is : $event->otp", $event->fromName);
    }
}
