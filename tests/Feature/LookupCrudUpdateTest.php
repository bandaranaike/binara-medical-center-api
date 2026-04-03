<?php

namespace Tests\Feature;

use App\Models\Allergy;
use App\Models\Disease;
use App\Models\Role;
use App\Models\TrustedSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LookupCrudUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_an_allergy_without_a_route_bound_model_instance(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $trustedSite = TrustedSite::query()->create([
            'domain' => 'admin.local',
            'api_key' => 'trusted-api-key',
        ]);

        $allergy = Allergy::query()->create([
            'name' => 'Dust',
        ]);

        $response = $this->withHeaders($this->trustedHeaders($trustedSite))
            ->putJson("/api/allergies/{$allergy->id}", [
                'name' => 'Pollen',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Record updated successfully',
            ]);

        $this->assertDatabaseHas('allergies', [
            'id' => $allergy->id,
            'name' => 'Pollen',
        ]);
    }

    public function test_admin_can_update_a_disease_without_a_route_bound_model_instance(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $trustedSite = TrustedSite::query()->create([
            'domain' => 'admin.local',
            'api_key' => 'trusted-api-key',
        ]);

        $disease = Disease::query()->create([
            'name' => 'Flu',
        ]);

        $response = $this->withHeaders($this->trustedHeaders($trustedSite))
            ->putJson("/api/diseases/{$disease->id}", [
                'name' => 'Migraine',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Record updated successfully',
            ]);

        $this->assertDatabaseHas('diseases', [
            'id' => $disease->id,
            'name' => 'Migraine',
        ]);
    }

    public function test_admin_can_list_users_when_a_user_has_no_role(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $trustedSite = TrustedSite::query()->create([
            'domain' => 'admin.local',
            'api_key' => 'trusted-api-key',
        ]);

        $orphanedUser = User::factory()->create([
            'role_id' => null,
        ]);

        $response = $this->withHeaders($this->trustedHeaders($trustedSite))
            ->getJson('/api/users');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $admin->id,
                'role' => 'Admin',
            ])
            ->assertJsonFragment([
                'id' => $orphanedUser->id,
                'role' => null,
            ])
            ->assertJson(fn ($json) => $json
                ->has('data', 2)
                ->where('last_page', 1)
                ->etc());
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

    private function trustedHeaders(TrustedSite $trustedSite): array
    {
        return [
            'X-API-KEY' => $trustedSite->api_key,
            'Referer' => 'https://'.$trustedSite->domain,
        ];
    }
}
