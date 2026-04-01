<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BillRegistrationNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_creation_generates_a_uuid_without_registration_number_columns(): void
    {
        $bill = Bill::query()->create([
            'patient_id' => $this->createPatient()->id,
            'doctor_id' => $this->createDoctor()->id,
            'status' => BillStatus::DOCTOR,
            'date' => now(),
        ]);

        $this->assertNotNull($bill->fresh()->uuid);
        $this->assertFalse(Schema::hasColumn('bills', 'bill_registration_number'));
        $this->assertFalse(Schema::hasColumn('bills', 'booking_registration_number'));
    }

    public function test_bill_creation_for_bookings_still_persists_without_registration_number_columns(): void
    {
        $bill = Bill::query()->create([
            'patient_id' => $this->createPatient()->id,
            'doctor_id' => $this->createDoctor()->id,
            'status' => BillStatus::BOOKED,
            'date' => now(),
        ]);

        $this->assertNotNull($bill->fresh()->uuid);
        $this->assertSame(BillStatus::BOOKED->value, $bill->fresh()->status);
    }

    public function test_updating_a_bill_to_booked_does_not_require_registration_number_columns(): void
    {
        $bill = Bill::query()->create([
            'patient_id' => $this->createPatient()->id,
            'doctor_id' => $this->createDoctor()->id,
            'status' => BillStatus::TREATMENT,
            'date' => now(),
        ]);

        $bill->update([
            'status' => BillStatus::BOOKED,
        ]);

        $this->assertSame(BillStatus::BOOKED->value, $bill->fresh()->status);
        $this->assertNotNull($bill->fresh()->uuid);
    }

    private function createDoctor(): Doctor
    {
        $doctorRole = Role::query()->create([
            'name' => 'Doctor',
            'key' => 'doctor',
            'description' => 'Doctor role',
        ]);

        $specialty = Specialty::query()->create([
            'name' => 'General Medicine',
        ]);

        $hospital = Hospital::query()->create([
            'name' => 'Main Hospital',
            'location' => 'Colombo',
        ]);

        return Doctor::query()->create([
            'name' => 'Dr. Test',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000099',
            'email' => 'doctor@test.local',
            'doctor_type' => 'opd',
        ]);
    }

    private function createPatient(): Patient
    {
        return Patient::factory()->create();
    }
}
