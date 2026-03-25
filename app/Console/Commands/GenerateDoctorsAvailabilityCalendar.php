<?php

namespace App\Console\Commands;

use App\Models\DoctorAvailability;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDoctorsAvailabilityCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:calendar {--start= : Start date} {--end= : End date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Doctors Availability Calendar';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $startDateInput = $this->option('start');
        $endDateInput = $this->option('end');

        $startDate = $startDateInput ? Carbon::parse($startDateInput) : Carbon::now()->addMonth();
        $endDate = $endDateInput ? Carbon::parse($endDateInput) : $startDate->copy()->addDay();

        // Fetch all active doctor schedules
        $doctorSchedules = DoctorSchedule::where('status', 'active')->get();

        // Prepare an array to hold all events to be inserted/updated
        $doctorAvailabilities = [];

        foreach ($doctorSchedules as $schedule) {
            $dayOfWeek = $schedule->weekday;
            $time = $schedule->time;
            $maxSeats = $schedule->seats;

            // Generate all dates for the given weekday between start and end date
            $date = $startDate->copy()->next($dayOfWeek); // Find the next occurrence of the weekday
            while ($date->lte($endDate)) {
                $doctorAvailabilities[] = [
                    'doctor_id' => $schedule->doctor_id,
                    'date' => $date->toDateString(),
                    'time' => $time,
                    'seats' => $maxSeats,
                    'available_seats' => $maxSeats,
                    'doctor_schedule_id' => $schedule->id,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $date->addWeek(); // Move to the next week
            }
        }

        // Use bulk insert with conflict handling (update on duplicate)
        DB::transaction(function () use ($doctorAvailabilities) {
            foreach (array_chunk($doctorAvailabilities, 1000) as $chunk) {
                DB::table('doctor_availabilities')->upsert(
                    $chunk,
                    ['doctor_id', 'date', 'time'], // Unique key for conflict detection
                    ['seats', 'available_seats', 'status', 'updated_at'] // Columns to update on conflict
                );
            }
        });

        $this->info('Event calendar generated successfully.');

        $this->removeOldData();

        $this->info('Old event calendar data deleted successfully.');
    }

    private function removeOldData(): void
    {
        DoctorAvailability::where('date', '<', Carbon::now()->subDays(2))->delete();
    }
}
