<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePatientMedicineRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'bill_id' => 'required|exists:bills,id',
            'medicine_id' => 'required',
            'medication_frequency_name' => 'nullable|string',
            'duration' => 'nullable|string',
            'medicine_name' => 'nullable|string',
            'medication_frequency_id' => 'required',
        ];
    }
}
