<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AllergiesTableSeeder::class,
            DiseasesTableSeeder::class,
            DoctorSpecialtySeeder::class,
            DrugSeeder::class,
            HospitalSeeder::class,
            MedicationFrequencySeeder::class,
            MedicineSeeder::class,
            ServiceSeeder::class,
            RoleSeeder::class,
            TrustedSiteSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
