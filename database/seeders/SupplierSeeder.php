<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'Supplier A',
                'address' => '123 Main Street',
                'phone' => '0771234567',
                'email' => 'supplierA@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Supplier B',
                'address' => '456 High Street',
                'phone' => '0777654321',
                'email' => 'supplierB@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
