<?php

namespace Tests\Feature;

use App\Enums\BillPaymentStatus;
use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\TrustedSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DaySummaryReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_day_summary_returns_shift_filtered_printable_items(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $trustedSite = TrustedSite::query()->create([
            'domain' => 'admin.local',
            'api_key' => 'trusted-api-key',
        ]);

        $doctorAruna = $this->createDoctor('Dr.Aruna');
        $doctorBimal = $this->createDoctor('Dr.Bimal');
        $patient = Patient::factory()->create();

        $channeling = Service::query()->create([
            'name' => 'Channeling',
            'key' => 'channeling',
            'bill_price' => 2000,
            'system_price' => 500,
        ]);

        $dressing = Service::query()->create([
            'name' => 'Dressing',
            'key' => 'dressing',
            'bill_price' => 400,
            'system_price' => 0,
        ]);

        $this->createBillItemSet(
            patient: $patient,
            doctor: $doctorAruna,
            service: $channeling,
            date: '2026-04-01 09:00:00',
            shift: 'morning',
            paymentStatus: BillPaymentStatus::PAID->value,
            amounts: [2000, 2000],
        );

        $this->createBillItemSet(
            patient: $patient,
            doctor: $doctorBimal,
            service: $channeling,
            date: '2026-04-01 10:00:00',
            shift: 'morning',
            paymentStatus: BillPaymentStatus::PAID->value,
            amounts: [2000],
        );

        $this->createBillItemSet(
            patient: $patient,
            doctor: $doctorAruna,
            service: $dressing,
            date: '2026-04-01 11:00:00',
            shift: 'morning',
            paymentStatus: BillPaymentStatus::PAID->value,
            amounts: [400, 400, 0],
        );

        $this->createBillItemSet(
            patient: $patient,
            doctor: $doctorAruna,
            service: $dressing,
            date: '2026-04-01 18:00:00',
            shift: 'evening',
            paymentStatus: BillPaymentStatus::PAID->value,
            amounts: [999],
        );

        $this->createBillItemSet(
            patient: $patient,
            doctor: $doctorAruna,
            service: $dressing,
            date: '2026-04-01 12:00:00',
            shift: 'morning',
            paymentStatus: BillPaymentStatus::PENDING->value,
            amounts: [999],
        );

        $response = $this->withHeaders($this->trustedHeaders($trustedSite))
            ->getJson('/api/reports/day-summary?date=2026-04-01&shift=morning');

        $response->assertOk()
            ->assertJson([
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-01',
            ])
            ->assertJsonCount(3, 'items')
            ->assertJsonFragment([
                'service_name' => 'Channeling Dr.Aruna',
                'quantity' => 2,
                'total' => 4000.0,
            ])
            ->assertJsonFragment([
                'service_name' => 'Channeling Dr.Bimal',
                'quantity' => 1,
                'total' => 2000.0,
            ])
            ->assertJsonFragment([
                'service_name' => 'Dressing',
                'quantity' => 3,
                'total' => 800.0,
            ])
            ->assertJsonMissing([
                'service_name' => 'Evening Dressing',
            ])
            ->assertJsonMissing([
                'total' => 999.0,
            ]);
    }

    public function test_day_summary_defaults_date_to_today(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $trustedSite = TrustedSite::query()->create([
            'domain' => 'admin.local',
            'api_key' => 'trusted-api-key',
        ]);

        $doctor = $this->createDoctor('Dr.Today');
        $patient = Patient::factory()->create();
        $service = Service::query()->create([
            'name' => 'Dressing',
            'key' => 'dressing',
            'bill_price' => 400,
            'system_price' => 0,
        ]);

        $this->createBillItemSet(
            patient: $patient,
            doctor: $doctor,
            service: $service,
            date: now()->format('Y-m-d 09:00:00'),
            shift: 'morning',
            paymentStatus: BillPaymentStatus::PAID->value,
            amounts: [400],
        );

        $response = $this->withHeaders($this->trustedHeaders($trustedSite))
            ->getJson('/api/reports/day-summary?shift=morning');

        $response->assertOk()
            ->assertJson([
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
            ])
            ->assertJsonFragment([
                'service_name' => 'Dressing',
                'quantity' => 1,
                'total' => 400.0,
            ]);
    }

    private function createAdminUser(): User
    {
        $adminRole = Role::query()->create([
            'name' => 'Admin',
            'key' => 'admin',
            'description' => 'Admin role',
        ]);

        return User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
    }

    private function createDoctor(string $name): Doctor
    {
        $doctorRole = Role::query()->firstOrCreate(
            ['key' => 'doctor'],
            ['name' => 'Doctor', 'description' => 'Doctor role'],
        );

        $specialty = Specialty::query()->firstOrCreate(['name' => 'General']);
        $hospital = Hospital::query()->firstOrCreate(
            ['name' => 'Main Hospital'],
            ['location' => 'Colombo'],
        );

        return Doctor::query()->create([
            'name' => $name,
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => fake()->numerify('+9477#######'),
            'email' => fake()->unique()->safeEmail(),
            'doctor_type' => 'opd',
        ]);
    }

    /**
     * @param  list<int|float>  $amounts
     */
    private function createBillItemSet(
        Patient $patient,
        Doctor $doctor,
        Service $service,
        string $date,
        string $shift,
        string $paymentStatus,
        array $amounts,
    ): Bill {
        $bill = Bill::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => $date,
            'status' => BillStatus::DONE->value,
            'shift' => $shift,
            'payment_status' => $paymentStatus,
            'payment_type' => 'cash',
            'bill_amount' => array_sum($amounts),
            'system_amount' => 0,
            'appointment_type' => $service->name,
        ]);

        foreach ($amounts as $amount) {
            $bill->billItems()->create([
                'service_id' => $service->id,
                'bill_amount' => $amount,
                'system_amount' => 0,
            ]);
        }

        return $bill;
    }

    private function trustedHeaders(TrustedSite $trustedSite): array
    {
        return [
            'X-API-KEY' => $trustedSite->api_key,
            'Referer' => 'https://'.$trustedSite->domain,
        ];
    }
}
