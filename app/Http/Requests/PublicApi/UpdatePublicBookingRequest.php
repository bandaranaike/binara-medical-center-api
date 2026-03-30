<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\AppointmentType;
use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient.name' => ['required', 'string', 'max:255'],
            'patient.telephone' => ['required', 'string', 'max:20'],
            'patient.email' => ['nullable', 'email', 'max:255'],
            'patient.registration_no' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('patients', 'registration_no')->ignore($this->route('booking')?->patient_id),
            ],
            'patient.age' => ['required', 'numeric', 'min:0'],
            'patient.gender' => ['nullable', 'string', 'in:male,female,other'],
            'patient.address' => ['nullable', 'string', 'max:255'],
            'patient.birthday' => ['nullable', 'date'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'doctor_type' => ['required', 'string', Rule::in(AppointmentType::toArray())],
            'date' => ['required', 'date'],
            'shift' => ['required', 'string', 'in:morning,evening'],
            'payment_type' => ['required', 'string', Rule::in(PaymentType::toArray())],
            'service_type' => ['required', 'string', Rule::in(AppointmentType::toArray())],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'system_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
