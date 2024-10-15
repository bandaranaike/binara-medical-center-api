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
            [
                'name' => "Doctor default channeling fee",
                'key' => config('binara.channeling.default_doctor_fee_key'),
                'bill_price' => 2000,
                'system_price' => 0,
            ],
        ];

        Service::upsert($services, 'key');
    }
}
