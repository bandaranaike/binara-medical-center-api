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
            ['name' => 'OD', 'description' => 'Once Daily'],
            ['name' => 'BID', 'description' => 'Twice a Day'],
            ['name' => 'TID', 'description' => 'Three Times a Day'],
            ['name' => 'QID', 'description' => 'Four Times a Day'],
            ['name' => 'QHS', 'description' => 'At Bedtime'],
            ['name' => 'PRN', 'description' => 'As Needed'],
            ['name' => 'Q6H', 'description' => 'Every 6 Hours'],
            ['name' => 'STAT', 'description' => 'Immediately'],
        ];

        // Using upsert to ensure name is unique
        DB::table('medication_frequencies')->upsert(
            $medicationFrequencies,
            ['name'], // Unique constraint column
            ['description'] // Columns to update if a conflict occurs
        );
    }
}
