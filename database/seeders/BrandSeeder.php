<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('brands')->insert([
            [
                'name' => 'Brand A',
                'drug_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brand B',
                'drug_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
