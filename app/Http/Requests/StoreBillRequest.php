<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreBillRequest extends FormRequest
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
            'system_amount' => 'required|numeric',
            'bill_amount' => 'required|numeric',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'status' => 'required|string|max:255',
            'bill_items' => 'required|array',
            'bill_items.*.service_id' => 'required|exists:services,id',
            'bill_items.*.system_amount' => 'required|numeric',
            'bill_items.*.bill_amount' => 'required|numeric',
        ];
    }
}
