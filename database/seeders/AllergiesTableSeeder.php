<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AllergiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $allergies = [
            ['name' => 'Peanuts', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dust Mites', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Pollen', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Pet Dander', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Shellfish', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Latex', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Insect Stings', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Milk', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Eggs', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Penicillin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        DB::table('allergies')->insert($allergies);
    }
}
