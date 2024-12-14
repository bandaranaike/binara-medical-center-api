<?php

namespace Database\Seeders;

use App\Models\Service;
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
                'name' => "Doctor default channeling fee",
                'key' => Service::DEFAULT_SPECIALIST_CHANNELING_KEY,
                'bill_price' => 2500,
                'system_price' => 500,
            ],
            [
                'name' => "OPD doctor fee",
                'key' => Service::DEFAULT_DOCTOR_KEY,
                'bill_price' => 400,
                'system_price' => 100,
            ],
            [
                'name' => "Medicines",
                'key' => Service::MEDICINE_KEY,
                'bill_price' => 500,
                'system_price' => 0,
            ]
        ];

        Service::upsert($services, 'key');
    }
}
