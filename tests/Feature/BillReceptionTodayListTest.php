<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Patient;
use App\Models\Role;
use App\Models\TrustedSite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BillReceptionTodayListTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_date_defaults_to_today_in_colombo(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-25 01:00:00', 'UTC'));
        $this->authenticateReceptionUser();

        $includedBill = $this->createBill('2026-08-24 18:30:00');
        $this->createBill('2026-08-25 18:30:00');

        $response = $this->getJson('/api/bills/pending/reception', $this->apiHeaders());

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.id', $includedBill->id);
        $response->assertJsonPath('0.queue_date', '2026-08-24T18:30:00Z');
    }

    public function test_valid_date_returns_only_bills_in_that_colombo_calendar_day(): void
    {
        $this->authenticateReceptionUser();
        $includedBill = $this->createBill('2026-08-25 06:30:00');
        $this->createBill('2026-08-26 06:30:00');

        $response = $this->getJson('/api/bills/pending/reception?date=2026-08-25', $this->apiHeaders());

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.id', $includedBill->id);
        $response->assertJsonPath('0.status', 'reception');
        $response->assertJsonStructure([
            '*' => [
                'id', 'uuid', 'bill_amount', 'system_amount', 'queue_number',
                'patient_name', 'doctor_name', 'queue_date', 'status',
                'payment_status', 'payment_type', 'appointment_type',
            ],
        ]);
    }

    public function test_bills_immediately_before_and_after_colombo_midnight_are_assigned_correctly(): void
    {
        $this->authenticateReceptionUser();
        $beforeMidnight = $this->createBill('2026-08-24 18:29:59');
        $atMidnight = $this->createBill('2026-08-24 18:30:00');

        $previousDayResponse = $this->getJson('/api/bills/pending/reception?date=2026-08-24', $this->apiHeaders());
        $currentDayResponse = $this->getJson('/api/bills/pending/reception?date=2026-08-25', $this->apiHeaders());

        $previousDayResponse->assertJsonCount(1)->assertJsonPath('0.id', $beforeMidnight->id);
        $currentDayResponse->assertJsonCount(1)->assertJsonPath('0.id', $atMidnight->id);
    }

    public function test_invalid_date_returns_a_validation_error(): void
    {
        $this->authenticateReceptionUser();

        $response = $this->getJson('/api/bills/pending/reception?date=2026-02-30', $this->apiHeaders());

        $response->assertUnprocessable()->assertJsonValidationErrors('date');
    }

    public function test_reception_endpoint_still_requires_authorized_user(): void
    {
        $this->getJson('/api/bills/pending/reception', $this->apiHeaders())->assertUnauthorized();
    }

    private function authenticateReceptionUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Reception',
            'key' => 'reception',
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);

        Sanctum::actingAs($user);

        return $user;
    }

    private function createBill(string $createdAt): Bill
    {
        $bill = Bill::query()->create([
            'patient_id' => Patient::factory()->create()->id,
            'status' => 'reception',
            'payment_type' => 'cash',
            'payment_status' => 'pending',
            'appointment_type' => 'opd',
            'date' => '2026-08-25',
        ]);

        $bill->forceFill([
            'created_at' => Carbon::parse($createdAt, 'UTC'),
            'updated_at' => Carbon::parse($createdAt, 'UTC'),
        ])->saveQuietly();

        return $bill;
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(): array
    {
        TrustedSite::query()->firstOrCreate([
            'api_key' => 'test-api-key',
        ], [
            'domain' => 'localhost',
        ]);

        return [
            'Accept' => 'application/json',
            'X-API-KEY' => 'test-api-key',
            'Referer' => 'http://localhost',
        ];
    }
}
