<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\AppointmentType;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $doctorTypes = [
            AppointmentType::SPECIALIST->value,
            AppointmentType::DENTAL->value,
        ];

        return [
            'name' => ['required', 'string'],
            'phone' => ['required_if:user_id,null'],
            'email' => ['nullable', 'email'],
            'registration_no' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'age' => ['required', 'numeric', 'between:0,100'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'doctor_type' => ['required', 'string', 'in:'.implode(',', $doctorTypes)],
            'date' => ['required', 'date'],
            'user_id' => ['nullable', 'uuid'],
        ];
    }
}
