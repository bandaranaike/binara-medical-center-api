<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => "Other fee",
                'key' => config('binara.channeling.other_fee_key'),
                'bill_price' => 500,
                'system_price' => 500,
            ],
        ];

        Service::insert($services);
    }
}
