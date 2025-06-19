<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicationFrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicationFrequencies = [
            ['name' => 'OD', 'description' => 'Once Daily', 'frequency' => 1],
            ['name' => 'BID', 'description' => 'Twice a Day', 'frequency' => 2],
            ['name' => 'TID', 'description' => 'Three Times a Day', 'frequency' => 3],
            ['name' => 'QID', 'description' => 'Four Times a Day', 'frequency' => 4],
            ['name' => 'QHS', 'description' => 'At Bedtime', 'frequency' => 1],
            ['name' => 'PRN', 'description' => 'As Needed', 'frequency' => 1],
            ['name' => 'Q6H', 'description' => 'Every 6 Hours', 'frequency' => 4],
            ['name' => 'STAT', 'description' => 'Immediately', 'frequency' => 1],
        ];

        // Using upsert to ensure name is unique
        DB::table('medication_frequencies')->upsert(
            $medicationFrequencies,
            ['name'], // Unique constraint column
            ['description', 'frequency'] // Columns to update if a conflict occurs
        );
    }
}
