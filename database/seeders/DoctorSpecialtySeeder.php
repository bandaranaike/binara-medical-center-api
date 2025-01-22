<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorSpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('specialties')->upsert([
            ['name' => 'Cardiology'],
            ['name' => 'Dermatology'],
            ['name' => 'Endocrinology'],
            ['name' => 'Gastroenterology'],
            ['name' => 'General Surgery'],
            ['name' => 'Internal Medicine'],
            ['name' => 'Neurology'],
            ['name' => 'Obstetrics and Gynecology'],
            ['name' => 'Oncology'],
            ['name' => 'Ophthalmology'],
            ['name' => 'Orthopedic Surgery'],
            ['name' => 'Otolaryngology (ENT)'],
            ['name' => 'Pediatrics'],
            ['name' => 'Plastic Surgery'],
            ['name' => 'Psychiatry'],
            ['name' => 'Pulmonology'],
            ['name' => 'Radiology'],
            ['name' => 'Rheumatology'],
            ['name' => 'Urology'],
            ['name' => 'Nephrology'],
            ['name' => 'Hematology'],
            ['name' => 'Anesthesiology'],
            ['name' => 'Emergency Medicine'],
            ['name' => 'Geriatrics'],
            ['name' => 'Infectious Disease'],
            ['name' => 'Physical Medicine and Rehabilitation'],
            ['name' => 'Vascular Surgery'],
            ['name' => 'Thoracic Surgery'],
            ['name' => 'Neonatology'],
            ['name' => 'Nuclear Medicine'],
            ['name' => 'Pathology'],
            ['name' => 'Family Medicine'],
            ['name' => 'Public Health'],
            ['name' => 'Allergy and Immunology'],
            ['name' => 'Pediatric Surgery'],
            ['name' => 'Podiatry'],
            ['name' => 'Sports Medicine'],
            ['name' => 'Osteopathic Medicine'],
            ['name' => 'Proctology'],
            ['name' => 'Transplant Surgery'],
            ['name' => 'Genetics'],
            ['name' => 'Pharmacology'],
            ['name' => 'Forensic Medicine'],
            ['name' => 'Medical Toxicology'],
            ['name' => 'Sleep Medicine'],
            ['name' => 'Pain Medicine'],
            ['name' => 'Critical Care Medicine'],
            ['name' => 'Interventional Radiology'],
            ['name' => 'Occupational Medicine'],
            ['name' => 'Palliative Care'],
        ], ['name']);
    }
}
