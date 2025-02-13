<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('medicines')->upsert([
            [
                'name' => 'Paracetamol',
                'drug_name' => 'Acetaminophen',
                'expire_date' => $now->addMonths(12),
                'initial_quantity' => 500,
                'quantity' => 500,
                'price' => 0.50
            ],
            [
                'name' => 'Ibuprofen',
                'drug_name' => 'Ibuprofen',
                'expire_date' => $now->addMonths(18),
                'initial_quantity' => 300,
                'quantity' => 300,
                'price' => 0.80
            ],
            [
                'name' => 'Amoxicillin',
                'drug_name' => 'Amoxicillin',
                'expire_date' => $now->addMonths(24),
                'initial_quantity' => 200,
                'quantity' => 200,
                'price' => 1.20
            ],
            [
                'name' => 'Cetirizine',
                'drug_name' => 'Cetirizine Hydrochloride',
                'expire_date' => $now->addMonths(6),
                'initial_quantity' => 150,
                'quantity' => 150,
                'price' => 0.30,
            ],
            [
                'name' => 'Metformin',
                'drug_name' => 'Metformin Hydrochloride',
                'expire_date' => $now->addMonths(24),
                'initial_quantity' => 400,
                'quantity' => 400,
                'price' => 0.60,
            ],
            [
                'name' => 'Lisinopril',
                'drug_name' => 'Lisinopril',
                'expire_date' => $now->addMonths(36),
                'initial_quantity' => 250,
                'quantity' => 250,
                'price' => 0.75,
            ],
            [
                'name' => 'Amlodipine',
                'drug_name' => 'Amlodipine Besylate',
                'expire_date' => $now->addMonths(30),
                'initial_quantity' => 350,
                'quantity' => 350,
                'price' => 0.70,
            ],
            [
                'name' => 'Omeprazole',
                'drug_name' => 'Omeprazole',
                'expire_date' => $now->addMonths(24),
                'initial_quantity' => 450,
                'quantity' => 450,
                'price' => 0.65,
            ],
            [
                'name' => 'Atorvastatin',
                'drug_name' => 'Atorvastatin Calcium',
                'expire_date' => $now->addMonths(36),
                'initial_quantity' => 300,
                'quantity' => 300,
                'price' => 0.85,
            ],
            [
                'name' => 'Clopidogrel',
                'drug_name' => 'Clopidogrel Bisulfate',
                'expire_date' => $now->addMonths(18),
                'initial_quantity' => 200,
                'quantity' => 200,
                'price' => 1.00,
            ],
            [
                'name' => 'Losartan',
                'drug_name' => 'Losartan Potassium',
                'expire_date' => $now->addMonths(30),
                'initial_quantity' => 250,
                'quantity' => 250,
                'price' => 0.95,
            ],
            [
                'name' => 'Simvastatin',
                'drug_name' => 'Simvastatin',
                'expire_date' => $now->addMonths(36),
                'initial_quantity' => 180,
                'quantity' => 180,
                'price' => 0.90,
            ]
        ], ['name'], ['drug_name', 'expire_date', 'initial_quantity', 'quantity', 'price']);
    }
}
