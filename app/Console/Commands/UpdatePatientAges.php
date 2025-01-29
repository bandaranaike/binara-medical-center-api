<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdatePatientAges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient:update-ages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update patient ages';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $patients = Patient::select('name', 'id', 'age', 'birthday', 'created_at')->get();
        $updatedCount = 0;
        foreach ($patients as $patient) {

            if ($patient->birthday) {
                $age = Carbon::parse($patient->birthday)->age;
            } else {
                $createdAt = Carbon::parse($patient->created_at);
                $currentDate = Carbon::now();
                if ($currentDate->month === $createdAt->month && $currentDate->day === $createdAt->day) {
                    $age = $patient->age + 1;
                } else {
                    $age = $patient->age;
                }
            }
            if ($patient->age != $age) {
                $patient->age = $age;
                $patient->save();
                $updatedCount++;
            }
        }
        $this->info("Updated $updatedCount patient ages");
    }
}
