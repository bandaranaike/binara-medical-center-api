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
use Tests\TestCase;

class BillRegistrationNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_bill_creation_generates_bill_registration_number(): void
    {
        $bill = Bill::query()->create([
            'patient_id' => $this->createPatient()->id,
            'doctor_id' => $this->createDoctor()->id,
            'status' => BillStatus::DOCTOR,
            'date' => now(),
        ]);

        $this->assertSame(Bill::formatBillRegistrationNumber($bill->id), $bill->fresh()->bill_registration_number);
        $this->assertNull($bill->fresh()->booking_registration_number);
    }

    public function test_bill_creation_generates_booking_registration_number_for_bookings(): void
    {
        $bill = Bill::query()->create([
            'patient_id' => $this->createPatient()->id,
            'doctor_id' => $this->createDoctor()->id,
            'status' => BillStatus::BOOKED,
            'date' => now(),
        ]);

        $this->assertSame(Bill::formatBillRegistrationNumber($bill->id), $bill->fresh()->bill_registration_number);
        $this->assertSame(Bill::formatBookingRegistrationNumber($bill->id), $bill->fresh()->booking_registration_number);
    }

    public function test_updating_a_bill_to_booked_generates_booking_registration_number(): void
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

        $this->assertSame(Bill::formatBillRegistrationNumber($bill->id), $bill->fresh()->bill_registration_number);
        $this->assertSame(Bill::formatBookingRegistrationNumber($bill->id), $bill->fresh()->booking_registration_number);
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
