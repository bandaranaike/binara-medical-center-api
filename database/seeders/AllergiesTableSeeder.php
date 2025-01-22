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
            ['name' => 'Peanuts'],
            ['name' => 'Tree nuts'],
            ['name' => 'Milk'],
            ['name' => 'Eggs'],
            ['name' => 'Shellfish'],
            ['name' => 'Fish'],
            ['name' => 'Wheat'],
            ['name' => 'Soy'],
            ['name' => 'Sesame'],
            ['name' => 'Latex'],
            ['name' => 'Pollen'],
            ['name' => 'Dust mites'],
            ['name' => 'Mold'],
            ['name' => 'Animal dander'],
            ['name' => 'Insect stings'],
            ['name' => 'Medications'],
            ['name' => 'Fragrances'],
            ['name' => 'Cleaning products'],
            ['name' => 'Nickel'],
            ['name' => 'Cobalt'],
            ['name' => 'Preservatives'],
            ['name' => 'Dyes'],
            ['name' => 'Gluten'],
            ['name' => 'Alcohol'],
            ['name' => 'Chlorine'],
            ['name' => 'Perfumes'],
            ['name' => 'Synthetic fabrics'],
            ['name' => 'Citrus fruits'],
            ['name' => 'Chocolate'],
            ['name' => 'Spices'],
        ];

        DB::table('allergies')->upsert($allergies, ['name']);
    }
}
