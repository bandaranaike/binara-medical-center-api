<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:reception,patient,pharmacy,doctor,nurse'],
        ]);

        $roleId = Role::where("key", $validatedData['role'])->select(['id'])->first()?->id;

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
        ]);

        return $this->sendToken($request);
    }

    // Laravel controller example
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        return $this->sendToken($request);
    }

    private function sendToken($request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('API Token')->plainTextToken;

            return new JsonResponse([
                'message' => 'Logged in successfully',
                'token' => $token,
                'role' => $user->role?->key,
                'name' => $user->name
            ], 200);
        }
        return new JsonResponse(['message' => 'Unauthorized'], 401);
    }

}
