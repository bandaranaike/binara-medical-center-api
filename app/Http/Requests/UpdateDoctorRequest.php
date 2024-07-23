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
            'specialty_id' => 'sometimes|required|exists:specialties,id',
            'telephone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:doctors,email,' . $this->doctor->id,
            'age' => 'sometimes|required|integer|min:1',
            'address' => 'sometimes|required|string|max:255',
        ];
    }
}
