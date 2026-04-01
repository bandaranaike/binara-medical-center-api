<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    private const STAFF_ROLES = [
        UserRole::ADMIN->value,
        UserRole::DOCTOR->value,
        UserRole::NURSE->value,
        UserRole::PHARMACY->value,
        UserRole::PHARMACY_ADMIN->value,
        UserRole::RECEPTION->value,
    ];

    public function register(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|lowercase|email|max:255|unique:".User::class,
            "password" => ["required", "confirmed", Password::defaults()],
            "role" => ["required", "string", "in:admin,reception,pharmacy,pharmacy_admin,doctor,nurse"],
        ]);

        $roleId = Role::where("key", $validatedData["role"])->select(["id"])->first()?->id;

        User::create([
            "name" => $validatedData["name"],
            "email" => $validatedData["email"],
            "password" => Hash::make($validatedData["password"]),
            "role_id" => $roleId,
        ]);

        return $this->authenticateSession(
            $request,
            $validatedData["email"],
            $validatedData["password"]
        );
    }

    public function login(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            "email" => "required|string|email",
            "password" => "required|string",
            "remember" => "nullable|boolean",
        ]);

        return $this->authenticateSession(
            $request,
            $validatedData["email"],
            $validatedData["password"],
            (bool) ($validatedData["remember"] ?? false)
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard("web")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return new JsonResponse(["message" => "Logged out successfully"]);
    }

    public function checkUserSession(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return new JsonResponse(["message" => "Unauthenticated."], 401);
        }

        $user->loadMissing("role");

        if (! $this->isStaffUser($user)) {
            Auth::guard("web")->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return new JsonResponse(["message" => "This account cannot access the staff application."], 403);
        }

        return new JsonResponse($this->formatUser($user));
    }

    private function authenticateSession(
        Request $request,
        string $email,
        string $password,
        bool $remember = false
    ): JsonResponse {
        if (! Auth::attempt(["email" => $email, "password" => $password], $remember)) {
            return new JsonResponse(["message" => "Provided credentials are invalid."], 401);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user instanceof User) {
            return new JsonResponse(["message" => "Unable to start an authenticated session."], 500);
        }

        $user->loadMissing("role");

        if (! $this->isStaffUser($user)) {
            Auth::guard("web")->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return new JsonResponse(["message" => "This account cannot access the staff application."], 403);
        }

        return new JsonResponse($this->formatUser($user));
    }

    private function isStaffUser(User $user): bool
    {
        return in_array($user->role?->key, self::STAFF_ROLES, true);
    }

    private function formatUser(User $user): array
    {
        return [
            "name" => $user->name,
            "role" => $user->role?->key,
        ];
    }
}
