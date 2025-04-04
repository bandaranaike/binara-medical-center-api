<?php

namespace App\Http\Requests\Website;

use App\Enums\AppointmentType;
use App\Models\Doctor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {

        $doctorTypes = [AppointmentType::SPECIALIST->value, AppointmentType::DENTAL->value];

        return [
            'name' => 'required|string',
            'phone' => 'required_if:user_id,null',
            'email' => 'nullable|email',
            'age' => 'required|numeric|between:0,100',
            'doctor_id' => 'required|exists:doctors,id',
            'doctor_type' => 'required|string|in:' . implode(',', $doctorTypes),
            'date' => 'required|date',
        ];
    }
}
