<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('hospitals')->upsert([
            [
                'name' => 'No hospital',
                'location' => 'There is no hospital',
            ],
            [
                'name' => 'Kandy',
                'location' => 'Kandy, Sri Lanka',
            ],
            [
                'name' => 'Peradeniya',
                'location' => 'Kandy, Sri Lanka',
            ]
        ], ['name'], ['location']);
    }
}
