<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\AppointmentType;
use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicBillRequest extends FormRequest
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
        return [
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'payment_type' => ['required', 'string', 'in:'.implode(',', PaymentType::toArray())],
            'system_amount' => ['required', 'numeric', 'min:0'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'is_booking' => ['required', 'boolean'],
            'service_type' => ['required', 'string', 'in:'.implode(',', AppointmentType::toArray())],
            'shift' => ['required', 'string', 'in:morning,evening'],
            'date' => ['required', 'date'],
        ];
    }
}
