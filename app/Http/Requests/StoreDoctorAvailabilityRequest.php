<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDoctorAvailabilityRequest extends FormRequest
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
            'doctor_id' => 'required|integer|exists:doctors,id',
            'status' => 'required|string|in:active,canceled',
            'date' => 'required|date',
            'seats' => 'required|integer',
            'available_seats' => 'required|integer',
            'time' => 'required|string',
        ];
    }
}
