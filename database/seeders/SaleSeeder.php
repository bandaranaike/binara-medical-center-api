<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sales')->insert([
            [
                'brand_id' => 1,
                'bill_id' => 1,
                'quantity' => 2,
                'total_price' => 500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand_id' => 2,
                'bill_id' => 1,
                'quantity' => 3,
                'total_price' => 750.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
