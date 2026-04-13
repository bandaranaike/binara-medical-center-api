<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Role;
use App\Models\TrustedSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HolidayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_holidays(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $trustedSite = $this->createTrustedSite();

        $storeResponse = $this->withHeaders($this->trustedHeaders($trustedSite))
            ->postJson('/api/holidays', [
                'name' => 'New Year',
                'date' => '2026-01-01',
                'message' => 'Closed for the holiday',
                'is_closed' => true,
            ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('message', 'Record created successfully')
            ->assertJsonPath('item.name', 'New Year')
            ->assertJsonPath('item.date', '2026-01-01T00:00:00.000000Z')
            ->assertJsonPath('item.message', 'Closed for the holiday')
            ->assertJsonPath('item.is_closed', true);

        $holidayId = $storeResponse->json('item.id');

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->getJson('/api/holidays')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $holidayId,
                'name' => 'New Year',
            ])
            ->assertJson(fn ($json) => $json
                ->where('last_page', 1)
                ->etc());

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->getJson("/api/holidays/{$holidayId}")
            ->assertOk()
            ->assertJsonPath('id', $holidayId)
            ->assertJsonPath('name', 'New Year')
            ->assertJsonPath('date', '2026-01-01T00:00:00.000000Z')
            ->assertJsonPath('message', 'Closed for the holiday')
            ->assertJsonPath('is_closed', true);

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->putJson("/api/holidays/{$holidayId}", [
                'name' => 'Poya Day',
                'date' => '2026-01-02',
                'message' => null,
                'is_closed' => false,
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Record updated successfully',
            ]);

        $this->assertDatabaseHas('holidays', [
            'id' => $holidayId,
            'name' => 'Poya Day',
            'date' => '2026-01-02 00:00:00',
            'message' => null,
            'is_closed' => false,
        ]);

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->deleteJson("/api/holidays/{$holidayId}")
            ->assertOk()
            ->assertJson([
                'message' => 'Record deleted successfully',
            ]);

        $this->assertDatabaseMissing('holidays', [
            'id' => $holidayId,
        ]);
    }

    public function test_holiday_date_must_be_unique_when_creating_or_updating(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);
        $trustedSite = $this->createTrustedSite();

        $existingHoliday = Holiday::query()->create([
            'name' => 'Independence Day',
            'date' => '2026-02-04',
            'message' => 'Closed',
            'is_closed' => true,
        ]);

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->postJson('/api/holidays', [
                'name' => 'Another Holiday',
                'date' => '2026-02-04',
                'message' => null,
                'is_closed' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date']);

        $anotherHoliday = Holiday::query()->create([
            'name' => 'May Day',
            'date' => '2026-05-01',
            'message' => null,
            'is_closed' => true,
        ]);

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->putJson("/api/holidays/{$anotherHoliday->id}", [
                'name' => 'May Day Updated',
                'date' => '2026-02-04',
                'message' => null,
                'is_closed' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date']);

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->putJson("/api/holidays/{$existingHoliday->id}", [
                'name' => 'Independence Day Updated',
                'date' => '2026-02-04',
                'message' => 'Updated message',
                'is_closed' => true,
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Record updated successfully',
            ]);
    }

    public function test_today_status_returns_the_matching_closed_holiday(): void
    {
        Holiday::query()->create([
            'name' => 'Clinic Maintenance',
            'date' => now()->toDateString(),
            'message' => 'Closed today',
            'is_closed' => true,
        ]);

        $trustedSite = $this->createTrustedSite();

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->getJson('/api/holidays/today-status')
            ->assertOk()
            ->assertJson([
                'date' => now()->toDateString(),
                'is_closed' => true,
                'holiday_name' => 'Clinic Maintenance',
                'message' => 'Closed today',
            ]);
    }

    public function test_today_status_returns_open_state_when_no_closed_holiday_exists(): void
    {
        Holiday::query()->create([
            'name' => 'Optional Event',
            'date' => now()->toDateString(),
            'message' => 'Open as usual',
            'is_closed' => false,
        ]);

        $trustedSite = $this->createTrustedSite();

        $this->withHeaders($this->trustedHeaders($trustedSite))
            ->getJson('/api/holidays/today-status')
            ->assertOk()
            ->assertJson([
                'date' => now()->toDateString(),
                'is_closed' => false,
                'holiday_name' => null,
                'message' => null,
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

    private function createTrustedSite(): TrustedSite
    {
        return TrustedSite::query()->create([
            'domain' => 'admin.local',
            'api_key' => 'trusted-api-key',
        ]);
    }

    private function trustedHeaders(TrustedSite $trustedSite): array
    {
        return [
            'X-API-KEY' => $trustedSite->api_key,
            'Referer' => 'https://'.$trustedSite->domain,
        ];
    }
}
