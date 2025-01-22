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
            ['name' => 'Diabetes'],
            ['name' => 'Hypertension'],
            ['name' => 'Asthma'],
            ['name' => 'Chronic Obstructive Pulmonary Disease (COPD)'],
            ['name' => 'Heart Disease'],
            ['name' => 'Stroke'],
            ['name' => 'Cancer'],
            ['name' => 'Arthritis'],
            ['name' => 'Osteoporosis'],
            ['name' => 'Chronic Kidney Disease'],
            ['name' => 'Hepatitis'],
            ['name' => 'Tuberculosis'],
            ['name' => 'HIV/AIDS'],
            ['name' => 'Alzheimer’s Disease'],
            ['name' => 'Parkinson’s Disease'],
            ['name' => 'Epilepsy'],
            ['name' => 'Depression'],
            ['name' => 'Anxiety Disorders'],
            ['name' => 'Bipolar Disorder'],
            ['name' => 'Schizophrenia'],
            ['name' => 'Obesity'],
            ['name' => 'Influenza'],
            ['name' => 'Pneumonia'],
            ['name' => 'COVID-19'],
            ['name' => 'Malaria'],
            ['name' => 'Dengue Fever'],
            ['name' => 'Typhoid Fever'],
            ['name' => 'Irritable Bowel Syndrome (IBS)'],
            ['name' => 'Celiac Disease'],
            ['name' => 'Crohn’s Disease'],
            ['name' => 'Ulcerative Colitis'],
            ['name' => 'Psoriasis'],
            ['name' => 'Eczema'],
            ['name' => 'Lupus'],
            ['name' => 'Multiple Sclerosis'],
            ['name' => 'Sickle Cell Disease'],
            ['name' => 'Hemophilia'],
            ['name' => 'Thyroid Disorders'],
            ['name' => 'Polycystic Ovary Syndrome (PCOS)'],
        ];

        DB::table('diseases')->upsert($diseases, ['name']);
    }
}
