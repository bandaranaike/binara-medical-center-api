<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DoctorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $doctors = [
            [
                'name' => 'Prof.Chathura Rathnayake',
                'hospital_id' => 3,
                'specialty_id' => 1, // Obstetrician-Gynecologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Wimalasiri Abeykoon',
                'hospital_id' => 1,
                'specialty_id' => 1, // Obstetrician-Gynecologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Ayodhya Senanayake',
                'hospital_id' => 3,
                'specialty_id' => 2, // Pediatrician
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Prof.Udaya Ralapanawa',
                'hospital_id' => 3,
                'specialty_id' => 3, // Physician
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Senthil Chandrasekaram',
                'hospital_id' => 3,
                'specialty_id' => 3, // Physician
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Sunil Bowattage',
                'hospital_id' => 2,
                'specialty_id' => 3, // Physician
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Nanda Amarasekara',
                'hospital_id' => 1,
                'specialty_id' => 4, // ENT
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.R D K Rajapaksha',
                'hospital_id' => 1,
                'specialty_id' => 5, // Eye Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Pradeep Narangoda',
                'hospital_id' => 1,
                'specialty_id' => 5, // Eye Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.E A D Udaya Kumara',
                'hospital_id' => 1,
                'specialty_id' => 6, // General Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Kosala Somarathne',
                'hospital_id' => 1,
                'specialty_id' => null, // No specialty specified
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Chandana Wijekoon',
                'hospital_id' => 1,
                'specialty_id' => 7, // Dermatologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Deepani Munidasa',
                'hospital_id' => 1,
                'specialty_id' => 7, // Dermatologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Priyanka Seneviwickrama',
                'hospital_id' => 3,
                'specialty_id' => 7, // Dermatologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Priyani Wanigasekara',
                'hospital_id' => 1,
                'specialty_id' => 8, // Rheumatologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Kamalika Gamage',
                'hospital_id' => 1,
                'specialty_id' => 8, // Rheumatologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Chandima Abeysinghe',
                'hospital_id' => 1,
                'specialty_id' => 9, // Radiologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Mudith Samaraweera',
                'hospital_id' => 1,
                'specialty_id' => 9, // Radiologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Nilushi Hewawasam',
                'hospital_id' => 3,
                'specialty_id' => null, // No specialty specified
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Kapila Rajasinghe',
                'hospital_id' => 2,
                'specialty_id' => 10, // Orthopeadic Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Ganga C Pathirana',
                'hospital_id' => 1,
                'specialty_id' => 11, // Venereologist
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Kamal Walgama',
                'hospital_id' => 1,
                'specialty_id' => 12, // Psychiatric
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Shanika Medagama',
                'hospital_id' => 1,
                'specialty_id' => 12, // Psychiatric
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Shanika Ekanayake',
                'hospital_id' => 1,
                'specialty_id' => 12, // Psychiatric
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.H L Horadagoda',
                'hospital_id' => 2,
                'specialty_id' => 13, // Neuro Physician
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr.Samadara Nakandala',
                'hospital_id' => 3,
                'specialty_id' => 14, // Chest Physician
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Prof.Dushantha Medagedara',
                'hospital_id' => 1,
                'specialty_id' => 14, // Chest Physician
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'specialist',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        Schema::disableForeignKeyConstraints();
        DB::table('doctors')->truncate();
        Schema::enableForeignKeyConstraints();
        DB::table('doctors')->insert($doctors);
    }
}
