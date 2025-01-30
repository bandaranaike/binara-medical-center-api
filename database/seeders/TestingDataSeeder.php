<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(9)->create();
        Doctor::factory(10)->create();
        Patient::factory(10)->create();
        Stock::factory(10)->create();
    }
}

