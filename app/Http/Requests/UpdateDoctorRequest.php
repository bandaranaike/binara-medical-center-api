<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDoctorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'hospital_id' => 'sometimes|required|exists:hospitals,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'specialty_id' => 'sometimes|exists:specialties,id',
            'telephone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|email',
        ];
    }
}
