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
            ['name' => 'Tree nuts', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Milk', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Eggs', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Shellfish', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fish', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Wheat', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Soy', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Sesame', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Latex', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Pollen', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dust mites', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Mold', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Animal dander', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Insect stings', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Medications', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fragrances', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Cleaning products', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Nickel', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Cobalt', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Preservatives', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dyes', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Gluten', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Alcohol', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chlorine', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Perfumes', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Synthetic fabrics', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Citrus fruits', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chocolate', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Spices', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        DB::table('allergies')->insert($allergies);
    }
}
