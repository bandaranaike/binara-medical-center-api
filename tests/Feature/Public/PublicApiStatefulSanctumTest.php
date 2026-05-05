<?php

namespace Tests\Feature\Public;

use App\Enums\AppointmentType;
use App\Enums\DoctorAvailabilityStatus;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Hospital;
use App\Models\PublicAppToken;
use App\Models\Role;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\TrustedSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiStatefulSanctumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Public machine-auth routes must not enter Sanctum's stateful CSRF flow.
     */
    public function test_public_booking_creation_succeeds_even_when_the_trusted_origin_is_stateful(): void
    {
        config()->set('sanctum.stateful', ['desktop.local']);

        $trustedSite = TrustedSite::query()->create([
            'domain' => 'desktop.local',
            'api_key' => 'trusted-api-key',
        ]);

        [, $plainTextToken] = PublicAppToken::issueForTrustedSite($trustedSite, 'Mobile App');

        $specialty = Specialty::query()->create(['name' => 'Cardiology']);
        $hospital = Hospital::query()->create(['name' => 'General Hospital', 'location' => 'Colombo']);
        $doctorRole = Role::query()->create([
            'name' => 'Doctor',
            'key' => 'doctor',
            'description' => 'Doctor role',
        ]);

        $doctor = Doctor::query()->create([
            'name' => 'Dr. Stateful',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000099',
            'email' => 'stateful@example.com',
            'doctor_type' => AppointmentType::SPECIALIST->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $doctor->id,
            'date' => '2026-05-10',
            'time' => '09:00:00',
            'seats' => 5,
            'available_seats' => 5,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        Service::query()->create([
            'name' => 'Specialist Channeling',
            'key' => 'channeling',
            'bill_price' => 2500,
            'system_price' => 500,
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-API-KEY' => $trustedSite->api_key,
            'Referer' => 'https://'.$trustedSite->domain,
            'Authorization' => 'Bearer '.$plainTextToken,
        ])->postJson('/api/public/bookings/make-appointment', [
            'name' => 'John Mobile',
            'phone' => '0771234567',
            'email' => 'john.mobile@example.com',
            'registration_no' => 'REG-MOBILE-001',
            'address' => 'Colombo 05',
            'age' => 30,
            'doctor_id' => $doctor->id,
            'doctor_type' => AppointmentType::SPECIALIST->value,
            'date' => '2026-05-10',
        ]);

        $response->assertOk()
            ->assertJsonPath('doctor_name', 'Dr. Stateful')
            ->assertJsonPath('date', '2026-05-10');
    }
}
