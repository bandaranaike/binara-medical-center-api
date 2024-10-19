<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diseases = [
            ['name' => 'Hypertension', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Diabetes Mellitus', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Asthma', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chronic Kidney Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Coronary Artery Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Osteoporosis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Alzheimer\'s Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Cancer', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Parkinson\'s Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Anemia', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        DB::table('diseases')->insert($diseases);
    }
}
