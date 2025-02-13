<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use CrudTrait;

    const DOCTOR_USER_PASSWORD = "password";

    public function __construct()
    {
        $this->model = new User();
        $this->updateRequest = new UpdateUserRequest();
        $this->storeRequest = new StoreUserRequest();
        $this->relationships = ['role:id,name'];
        $this->resource = UserResource::class;
    }

    public function createUserForDoctor(Request $request): JsonResponse
    {
        $data = $request->validate([
            "doctor_id" => "required|numeric|exists:doctors,id",
        ]);

        $doctor = Doctor::findOrFail($data['doctor_id']);
        if ($doctor->user_id) {
            return new JsonResponse(["status" => "failed", "message" => "Doctor already assigned to a user"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = User::create([
            'name' => $doctor->name,
            'email' => $doctor->email,
            'role_id' => Role::where('key', UserRole::DOCTOR->value)->first()->id,
            'password' => bcrypt(self::DOCTOR_USER_PASSWORD),
        ]);

        $doctor->user_id = $user->id;
        $doctor->save();

        return new JsonResponse($user);
    }
}
