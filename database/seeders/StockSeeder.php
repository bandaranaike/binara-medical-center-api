<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('stocks')->insert([
            [
                'brand_id' => 1,
                'supplier_id' => 1,
                'unit_price' => 25.00,
                'batch_number' => 'BN001',
                'initial_quantity' => 100,
                'quantity' => 90,
                'expire_date' => now()->addYear(),
                'cost' => 2500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'brand_id' => 2,
                'supplier_id' => 2,
                'unit_price' => 30.00,
                'batch_number' => 'BN002',
                'initial_quantity' => 200,
                'quantity' => 180,
                'expire_date' => now()->addYear(),
                'cost' => 6000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
