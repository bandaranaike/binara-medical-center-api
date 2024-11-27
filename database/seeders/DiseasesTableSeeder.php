<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diseases = [
            ['name' => 'Diabetes', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Hypertension', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Asthma', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chronic Obstructive Pulmonary Disease (COPD)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Heart Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Stroke', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Cancer', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Arthritis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Osteoporosis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chronic Kidney Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Hepatitis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Tuberculosis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'HIV/AIDS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Alzheimer’s Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Parkinson’s Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Epilepsy', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Depression', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Anxiety Disorders', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Bipolar Disorder', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Schizophrenia', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Obesity', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Influenza', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Pneumonia', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'COVID-19', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Malaria', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dengue Fever', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Typhoid Fever', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Irritable Bowel Syndrome (IBS)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Celiac Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Crohn’s Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Ulcerative Colitis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Psoriasis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Eczema', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Lupus', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Multiple Sclerosis', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Sickle Cell Disease', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Hemophilia', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Thyroid Disorders', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Polycystic Ovary Syndrome (PCOS)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        DB::table('diseases')->insert($diseases);
    }
}
