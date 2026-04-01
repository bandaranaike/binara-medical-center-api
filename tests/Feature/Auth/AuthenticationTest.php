<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\TrustedSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get("/login");

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $adminRole = $this->createRole(UserRole::ADMIN, "Administrator");
        $user = User::factory()->create(["role_id" => $adminRole->id]);

        $response = $this->post("/login", [
            "email" => $user->email,
            "password" => "password",
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route("dashboard", absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $adminRole = $this->createRole(UserRole::ADMIN, "Administrator");
        $user = User::factory()->create(["role_id" => $adminRole->id]);

        $this->post("/login", [
            "email" => $user->email,
            "password" => "wrong-password",
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $adminRole = $this->createRole(UserRole::ADMIN, "Administrator");
        $user = User::factory()->create(["role_id" => $adminRole->id]);

        $response = $this->actingAs($user)->post("/logout");

        $this->assertGuest();
        $response->assertRedirect("/");
    }

    public function test_staff_users_can_authenticate_through_the_api_login_and_bootstrap_their_session(): void
    {
        $adminRole = $this->createRole(UserRole::ADMIN, "Administrator");
        $user = User::factory()->create(["role_id" => $adminRole->id]);

        $this->createTrustedSite();

        $loginResponse = $this
            ->withHeader("Referer", "http://localhost:3000/login")
            ->withHeader("X-API-KEY", "test-api-key")
            ->postJson("/api/login", [
                "email" => $user->email,
                "password" => "password",
                "remember" => true,
            ]);

        $loginResponse
            ->assertOk()
            ->assertJson([
                "name" => $user->name,
                "role" => UserRole::ADMIN->value,
            ])
            ->assertJsonMissing(["token"]);

        $sessionResponse = $this
            ->withHeader("Referer", "http://localhost:3000/dashboard")
            ->withHeader("X-API-KEY", "test-api-key")
            ->getJson("/api/check-user-session");

        $sessionResponse->assertOk()->assertJson([
            "name" => $user->name,
            "role" => UserRole::ADMIN->value,
        ]);
    }

    public function test_staff_users_can_logout_through_the_api_logout(): void
    {
        $adminRole = $this->createRole(UserRole::ADMIN, "Administrator");
        $user = User::factory()->create(["role_id" => $adminRole->id]);

        $this->createTrustedSite();

        $this
            ->withHeader("Referer", "http://localhost:3000/login")
            ->withHeader("X-API-KEY", "test-api-key")
            ->postJson("/api/login", [
                "email" => $user->email,
                "password" => "password",
            ])
            ->assertOk();

        $response = $this
            ->withHeader("Referer", "http://localhost:3000/dashboard")
            ->withHeader("X-API-KEY", "test-api-key")
            ->postJson("/api/logout");

        $response->assertOk()->assertJson([
            "message" => "Logged out successfully",
        ]);
    }

    public function test_patient_accounts_can_not_authenticate_through_the_staff_api_login(): void
    {
        $patientRole = $this->createRole(UserRole::PATIENT, "Patient");
        $user = User::factory()->create(["role_id" => $patientRole->id]);

        $this->createTrustedSite();

        $response = $this
            ->withHeader("Referer", "http://localhost:3000/login")
            ->withHeader("X-API-KEY", "test-api-key")
            ->postJson("/api/login", [
                "email" => $user->email,
                "password" => "password",
            ]);

        $response
            ->assertForbidden()
            ->assertJson([
                "message" => "This account cannot access the staff application.",
            ]);

        $this->assertGuest();
    }

    private function createRole(UserRole $role, string $name): Role
    {
        return Role::create([
            "key" => $role->value,
            "name" => $name,
            "description" => $name,
        ]);
    }

    private function createTrustedSite(): TrustedSite
    {
        return TrustedSite::create([
            "domain" => "localhost",
            "api_key" => "test-api-key",
        ]);
    }
}
