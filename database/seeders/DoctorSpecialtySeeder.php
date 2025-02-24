<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorSpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('specialties')->upsert(
            [
                ['name' => 'Obstetrician-Gynecologist'],
                ['name' => 'Pediatrician'],
                ['name' => 'Physician'],
                ['name' => 'ENT'],
                ['name' => 'Eye Surgeon'],
                ['name' => 'General Surgeon'],
                ['name' => 'Dermatologist'],
                ['name' => 'Rheumatologist'],
                ['name' => 'Radiologist'],
                ['name' => 'Orthopedic Surgeon'],
                ['name' => 'Venereologist'],
                ['name' => 'Psychiatric'],
                ['name' => 'Neuro Physician'],
                ['name' => 'Chest Physician'],
                ['name' => 'Dental Surgeon'],
            ], ['name']);
    }
}
