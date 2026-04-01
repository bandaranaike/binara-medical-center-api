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
            'service_type' => ['required', 'string', Rule::in([...AppointmentType::toArray(), 'others'])],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'system_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.service_id' => ['nullable', 'integer'],
            'items.*.service_key' => ['nullable', 'string', 'max:80'],
            'items.*.service_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.bill_amount' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.system_amount' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.referred_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.category' => ['nullable', 'string', 'max:255'],
            'items.*.doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'items.*.is_ad_hoc' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('service_type') === 'others') {
            $this->merge(['service_type' => AppointmentType::TREATMENT->value]);
        }
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $items = $this->input('items', []);

                if (! is_array($items) || $items === []) {
                    return;
                }

                $itemBillAmount = collect($items)->sum(fn (array $item): float => (float) ($item['bill_amount'] ?? 0));
                $itemSystemAmount = collect($items)->sum(fn (array $item): float => (float) ($item['system_amount'] ?? 0));

                if (round($itemBillAmount, 2) !== round((float) $this->input('bill_amount'), 2)) {
                    $validator->errors()->add('bill_amount', 'The bill amount must equal the sum of item bill amounts.');
                }

                if (round($itemSystemAmount, 2) !== round((float) $this->input('system_amount'), 2)) {
                    $validator->errors()->add('system_amount', 'The system amount must equal the sum of item system amounts.');
                }

                foreach ($items as $index => $item) {
                    if (array_key_exists('service_id', $item) && (int) $item['service_id'] > 0) {
                        if (! \App\Models\Service::query()->whereKey((int) $item['service_id'])->exists()) {
                            $validator->errors()->add("items.$index.service_id", 'The selected service id is invalid.');
                        }
                    }
                }
            },
        ];
    }
}
