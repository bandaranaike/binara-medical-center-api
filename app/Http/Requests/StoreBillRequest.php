<?php

namespace App\Http\Requests;

use App\Enums\PaymentType;
use App\Models\Bill;
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
            'bill_amount' => 'required|numeric',
            'payment_type' => 'required|string|in:' . implode(",", PaymentType::toArray()),
            'system_amount' => 'required|numeric',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'is_booking' => 'required|boolean',
            'service_type' => 'required|string',
            'shift' => 'required|string',
            'date' => 'required_if:is_booking,true|date',
        ];
    }
}
