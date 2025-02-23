<?php

use App\Console\Commands\GenerateDoctorsAvailabilityCalendar;
use App\Console\Commands\UpdatePatientAges;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(UpdatePatientAges::class)->daily();
Schedule::command(GenerateDoctorsAvailabilityCalendar::class)->lastDayOfMonth();
