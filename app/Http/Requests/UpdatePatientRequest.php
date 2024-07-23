<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @property Patient $patient
 */
class UpdatePatientRequest extends FormRequest
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
            'age' => 'sometimes|required|integer|min:1',
            'address' => 'sometimes|string|max:255',
            'gender' => 'sometimes|string|max:10',
            'birthday' => 'sometimes|string|max:50',
            'telephone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|email|unique:patients,email,' . $this->patient->id,
        ];
    }
}
