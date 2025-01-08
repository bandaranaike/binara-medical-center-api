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
                'name' => "Specialist channeling",
                'key' => Service::DEFAULT_SPECIALIST_CHANNELING_KEY,
                'bill_price' => 2500,
                'system_price' => 500,
                'is_percentage' => false,
                'separate_items' => true,
            ],
            [
                'name' => "OPD doctor",
                'key' => Service::DEFAULT_DOCTOR_KEY,
                'bill_price' => 350,
                'system_price' => 100,
                'is_percentage' => false,
                'separate_items' => true,
            ],
            [
                'name' => "Medicines",
                'key' => Service::MEDICINE_KEY,
                'bill_price' => 0,
                'system_price' => 100,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Dental registration",
                'key' => Service::DENTAL_REGISTRATION_KEY,
                'bill_price' => 200,
                'system_price' => 100,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Dental treatments",
                'key' => Service::DENTAL_TREATMENTS_KEY,
                'bill_price' => 0,
                'system_price' => 50,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Dental lab",
                'key' => Service::DENTAL_LAB_KEY,
                'bill_price' => 0,
                'system_price' => 0,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Wound dressing",
                'key' => Service::WOUND_DRESSING_KEY,
                'bill_price' => 0,
                'system_price' => 100,
                'is_percentage' => true,
                'separate_items' => false,
            ]
        ];

        Service::upsert($services, 'key');
    }
}
