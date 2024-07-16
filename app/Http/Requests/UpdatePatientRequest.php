<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
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
            'age' => 'sometimes|required|integer|min:1',
            'address' => 'sometimes|required|string|max:255',
            'contact_number' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:patients,email,' . $this->patient->id,
        ];
    }
}
