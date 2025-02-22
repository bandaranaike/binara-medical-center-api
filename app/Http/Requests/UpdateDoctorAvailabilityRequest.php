<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDoctorAvailabilityRequest extends FormRequest
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
            'doctor_id' => 'sometimes|integer|exists:doctors,id',
            'status' => 'sometimes|string|in:active,canceled',
            'date' => 'sometimes|date',
            'seats' => 'sometimes|integer',
            'available_seats' => 'sometimes|integer',
            'time' => 'sometimes|string',
        ];
    }
}
