<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProceedPublicBookingPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_type' => ['required', 'string', Rule::in(PaymentType::toArray())],
            'shift' => ['required', 'string', 'in:morning,evening'],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'system_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
