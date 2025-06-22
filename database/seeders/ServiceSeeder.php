<?php

namespace Database\Seeders;

use App\Enums\ServiceKey;
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
                'key' => ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value,
                'bill_price' => 2500,
                'system_price' => 500,
                'is_percentage' => false,
                'separate_items' => true,
            ],
            [
                'name' => "OPD doctor",
                'key' => ServiceKey::DEFAULT_DOCTOR->value,
                'bill_price' => 350,
                'system_price' => 100,
                'is_percentage' => false,
                'separate_items' => true,
            ],
            [
                'name' => "Medicines",
                'key' => ServiceKey::MEDICINE->value,
                'bill_price' => 0,
                'system_price' => 100,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Dental registration",
                'key' => ServiceKey::DENTAL_REGISTRATION->value,
                'bill_price' => 200,
                'system_price' => 100,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Dental treatments",
                'key' => ServiceKey::DENTAL_TREATMENTS->value,
                'bill_price' => 0,
                'system_price' => 50,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Dental lab",
                'key' => ServiceKey::DENTAL_LAB->value,
                'bill_price' => 0,
                'system_price' => 0,
                'is_percentage' => true,
                'separate_items' => false,
            ],
            [
                'name' => "Wound dressing",
                'key' => ServiceKey::WOUND_DRESSING->value,
                'bill_price' => 0,
                'system_price' => 100,
                'is_percentage' => true,
                'separate_items' => false,
            ]
        ];

        ServiceKey::upsert($services, 'key');
    }
}
