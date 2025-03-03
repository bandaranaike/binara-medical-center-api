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
        $esmsService = new DialogESMSService(env('DIALOG_URL_MESSAGE_KEY'));
        $esmsService->sendMessage($event->phone, "Your OTP is : $event->otp", $event->fromName);
    }
}
