<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\WebUserProfileUpdateRequest;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class PatientAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|lowercase|email|max:255|unique:' . User::class,
            'phone' => 'nullable|string|max:20|unique:' . User::class,
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'email.required_without' => 'The email or phone field is required.',
            'phone.required_without' => 'The phone or email field is required.',
        ]);

        // Ensure at least one of email or phone is provided
        $request->validate([
            'email' => 'required_without:phone',
            'phone' => 'required_without:email',
        ]);

        $roleId = Role::where("key", UserRole::PATIENT->value)->select(['id'])->first()?->id;

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $roleId,
        ]);

        $this->createPatientIfNotExists($user);

        return $this->loginAndSendToken($validatedData['email'], $validatedData['password']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        // Revoke all tokens...
        $request->user()->tokens()->delete();

        return new JsonResponse(['message' => 'Logged out successfully']);
    }

    public function login(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($validatedData['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        return $this->loginAndSendToken($validatedData['username'], $validatedData['password'], $field, $request->get('remember'));
    }

    private function loginAndSendToken($userName, $password, $usernameField = 'email', $rememberMe = false): JsonResponse
    {

        if (Auth::attempt([$usernameField => $userName, 'password' => $password], $rememberMe)) {
            $user = Auth::user();

            $token = $user->createToken('API Token')->plainTextToken;

            $userDataArray = [
                'name' => $user->name,
                'id' => $user->uuid,
                'email' => $user->email,
                'phone' => $user->phone,
                'message' => 'Logged in successfully',
                'token' => $token
            ];

            return new JsonResponse($userDataArray, 200);
        }
        return new JsonResponse(['message' => "Invalid $usernameField or password."], 401);
    }

    private function createPatientIfNotExists(User $user): void
    {
        $patient = Patient::where(function ($query) use ($user) {
            $query->where('telephone', $user->phone)->whereNotNull('telephone');
        })->orWhere(function ($query) use ($user) {
            $query->where('email', $user->email)->whereNotNull('email');
        })->first();

        if (!$patient) {
            Patient::create([
                "user_id" => $user->id,
                "name" => $user->name,
                "telephone" => $user->phone,
                "email" => $user->email,
                "age" => 0,
            ]);
        } else {
            $patient->update(["user_id" => $user->id]);
        }
    }

    public function updateProfile(WebUserProfileUpdateRequest $request): JsonResponse
    {
        if (Auth::user()->update([$request->validated()])) {
            return new JsonResponse(['message' => "Profile updated successfully"], 200);
        }
        return new JsonResponse(['message' => "Something went wrong"], 401);
    }

}
