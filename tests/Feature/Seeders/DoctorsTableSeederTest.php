<?php

namespace Tests\Feature\Seeders;

use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Specialty;
use Database\Seeders\DoctorSpecialtySeeder;
use Database\Seeders\DoctorsTableSeeder;
use Database\Seeders\HospitalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorsTableSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctors_table_seeder_resolves_foreign_keys_by_seeded_names_instead_of_assumed_ids(): void
    {
        Specialty::query()->create(['name' => 'Legacy Specialty']);
        Hospital::query()->create([
            'name' => 'Legacy Hospital',
            'location' => 'Legacy Location',
        ]);

        $this->seed([
            DoctorSpecialtySeeder::class,
            HospitalSeeder::class,
            DoctorsTableSeeder::class,
        ]);

        $this->assertGreaterThan(0, Doctor::query()->count());

        $doctor = Doctor::query()->where('name', 'Prof.Chathura Rathnayake')->first();

        $this->assertNotNull($doctor);
        $this->assertSame('Peradeniya', $doctor->hospital?->name);
        $this->assertSame('Obstetrician-Gynecologist', $doctor->specialty?->name);
    }
}
