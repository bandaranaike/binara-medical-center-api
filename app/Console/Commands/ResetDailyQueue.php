<?php

namespace App\Console\Commands;

use App\Models\DailyPatientQueue;
use Illuminate\Console\Command;

class ResetDailyQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-daily-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily patient queue and order numbers';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Clear previous day's entries
        DailyPatientQueue::where('queue_date', '<', now()->toDateString())->delete();

        $this->info('Daily queues have been reset.');
    }
}
