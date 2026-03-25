<?php

namespace Database\Seeders;

use App\Models\Hospital;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DoctorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hospitalNamesByLegacyId = [
            1 => 'No hospital',
            2 => 'Kandy',
            3 => 'Peradeniya',
        ];

        $specialtyNamesByLegacyId = [
            1 => 'Obstetrician-Gynecologist',
            2 => 'Pediatrician',
            3 => 'Physician',
            4 => 'ENT',
            5 => 'Eye Surgeon',
            6 => 'General Surgeon',
            7 => 'Dermatologist',
            8 => 'Rheumatologist',
            9 => 'Radiologist',
            10 => 'Orthopedic Surgeon',
            11 => 'Venereologist',
            12 => 'Psychiatric',
            13 => 'Neuro Physician',
            14 => 'Chest Physician',
            15 => 'Dental Surgeon',
            16 => 'OPD',
        ];

        $hospitalIdsByName = Hospital::query()->pluck('id', 'name');
        $specialtyIdsByName = Specialty::query()->pluck('id', 'name');

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
            ], // Count 27
            [
                'name' => 'Dr Janak B Abayakoon',
                'hospital_id' => 1,
                'specialty_id' => 15, // Dental Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'dental',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr Ranjith Rajasinghe',
                'hospital_id' => 1,
                'specialty_id' => 15, // Dental Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'dental',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr Ranjith Waidyathilake',
                'hospital_id' => 1,
                'specialty_id' => 15, // Dental Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'dental',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr Mrs. Sureni Manoratna',
                'hospital_id' => 1,
                'specialty_id' => 15, // Dental Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'dental',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dr A Chandrathilake',
                'hospital_id' => 1,
                'specialty_id' => 15, // Dental Surgeon
                'user_id' => null,
                'telephone' => 0,
                'doctor_type' => 'dental',
                'email' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ], // Count 32

            ['name' => 'Dr W Kularathne', 'hospital_id' => 1, 'specialty_id' => 16, 'user_id' => null, 'telephone' => 0, 'doctor_type' => 'opd', 'email' => '', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dr Sampath Athapaththu', 'hospital_id' => 1, 'specialty_id' => 16, 'user_id' => null, 'telephone' => 0, 'doctor_type' => 'opd', 'email' => '', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dr L.W.N Kularathne', 'hospital_id' => 1, 'specialty_id' => 16, 'user_id' => null, 'telephone' => 0, 'doctor_type' => 'opd', 'email' => '', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dr P.A.D Manjula Senarachchi', 'hospital_id' => 1, 'specialty_id' => 16, 'user_id' => null, 'telephone' => 0, 'doctor_type' => 'opd', 'email' => '', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Dr (Mrs) S.S.V Vidanapathirana', 'hospital_id' => 1, 'specialty_id' => 16, 'user_id' => null, 'telephone' => 0, 'doctor_type' => 'opd', 'email' => '', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

        ];

        $doctors = array_map(function (array $doctor) use (
            $hospitalIdsByName,
            $hospitalNamesByLegacyId,
            $specialtyIdsByName,
            $specialtyNamesByLegacyId
        ): array {
            $hospitalName = $hospitalNamesByLegacyId[$doctor['hospital_id']] ?? null;

            if ($hospitalName === null || ! isset($hospitalIdsByName[$hospitalName])) {
                throw new \RuntimeException('Unable to resolve hospital for doctor seed: '.$doctor['name']);
            }

            $doctor['hospital_id'] = $hospitalIdsByName[$hospitalName];

            if ($doctor['specialty_id'] !== null) {
                $specialtyName = $specialtyNamesByLegacyId[$doctor['specialty_id']] ?? null;

                if ($specialtyName === null || ! isset($specialtyIdsByName[$specialtyName])) {
                    throw new \RuntimeException('Unable to resolve specialty for doctor seed: '.$doctor['name']);
                }

                $doctor['specialty_id'] = $specialtyIdsByName[$specialtyName];
            }

            return $doctor;
        }, $doctors);

        Schema::disableForeignKeyConstraints();
        DB::table('doctors')->truncate();
        Schema::enableForeignKeyConstraints();
        DB::table('doctors')->insert($doctors);
    }
}
