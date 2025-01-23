<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDoctorRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'hospital_id' => 'nullable|exists:hospitals,id',
            'user_id' => 'nullable|required|exists:users,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|unique:doctors,email',
            'doctor_type' => 'string',
        ];
    }
}
