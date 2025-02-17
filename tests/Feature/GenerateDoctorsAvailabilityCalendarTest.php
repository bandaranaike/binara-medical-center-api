<?php

namespace Tests\Feature;

use App\Console\Commands\GenerateDoctorsAvailabilityCalendar;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GenerateDoctorsAvailabilityCalendarTest extends TestCase
{
    use DatabaseTransactions;

    // Use database transactions for testing

    public function test_generate_doctors_availability_calendar_command()
    {
        // 1. Setup: Create some doctor schedules
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addMonths();

        $doctorSchedule1 = DoctorSchedule::factory()->create([
            'weekday' => $startDate->copy()->next(Carbon::MONDAY)->dayOfWeek, // Monday
            'time' => '09:00',
            'seats' => 10,
            'status' => 'active',
        ]);

        $doctorSchedule2 = DoctorSchedule::factory()->create([
            'weekday' => $startDate->copy()->next(Carbon::WEDNESDAY)->dayOfWeek, // Wednesday
            'time' => '14:00',
            'seats' => 5,
            'status' => 'active',
        ]);

        $doctorScheduleInactive = DoctorSchedule::factory()->create([
            'weekday' => $startDate->copy()->next(Carbon::FRIDAY)->dayOfWeek, // Friday
            'time' => '10:00',
            'seats' => 2,
            'status' => 'inactive', //This should not be picked
        ]);


        // 2. Run the command
        $this->artisan(GenerateDoctorsAvailabilityCalendar::class)
            ->assertSuccessful()
            ->expectsOutput('Event calendar generated successfully.');

        // 3. Assertions: Check if the events were created/updated correctly
        $expectedEventsCount = 0;
        $date = $startDate->copy()->next(Carbon::MONDAY);
        while ($date->lte($endDate)) {
            $expectedEventsCount++;
            $this->assertDatabaseHas('doctor_availabilities', [
                'doctor_id' => $doctorSchedule1->doctor_id,
                'date' => $date->toDateString(),
                'time' => '09:00',
                'seats' => 10,
                'available_seats' => 10,
                'status' => 'Active',
            ]);
            $date->addWeek();
        }

        $date = $startDate->copy()->next(Carbon::WEDNESDAY);
        while ($date->lte($endDate)) {
            $expectedEventsCount++;
            $this->assertDatabaseHas('doctor_availabilities', [
                'doctor_id' => $doctorSchedule2->doctor_id,
                'date' => $date->toDateString(),
                'time' => '14:00',
                'seats' => 5,
                'available_seats' => 5,
                'status' => 'Active',
            ]);
            $date->addWeek();
        }

        $this->assertDatabaseCount('doctor_availabilities', $expectedEventsCount);


        // 4. Test Update functionality (if an event already exists)
        $existingEvent = DB::table('doctor_availabilities')->where('doctor_id', $doctorSchedule1->doctor_id)->first();

        DB::table('doctor_availabilities')->where('doctor_id', $doctorSchedule1->doctor_id)->update([
            'available_seats' => 5,
            'status' => 'Cancelled',
        ]);

        $this->artisan(GenerateDoctorsAvailabilityCalendar::class);

        $this->assertDatabaseHas('doctor_availabilities', [
            'doctor_id' => $doctorSchedule1->doctor_id,
            'date' => $existingEvent->date,
            'time' => '09:00',
            'seats' => 10,
            'available_seats' => 10, // Available seats should be reset
            'status' => 'Active', // Status should be reset
        ]);

        $this->assertDatabaseMissing('doctor_availabilities', [
            'doctor_id' => $doctorScheduleInactive->doctor_id,
        ]);

    }
}
