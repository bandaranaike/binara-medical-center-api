<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\AppointmentType;
use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'payment_type' => ['required', 'string', Rule::in(PaymentType::toArray())],
            'system_amount' => ['required', 'numeric', 'min:0'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => [
                Rule::requiredIf(fn (): bool => (bool) $this->boolean('is_booking') || $this->input('service_type') !== AppointmentType::TREATMENT->value),
                'nullable',
                'integer',
                'exists:doctors,id',
            ],
            'is_booking' => ['required', 'boolean'],
            'service_type' => ['required', 'string', Rule::in([...AppointmentType::toArray(), 'others'])],
            'shift' => ['required', 'string', 'in:morning,evening'],
            'date' => ['required', 'date'],
            'items' => ['nullable', 'array', 'min:1'],
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

                    if (! array_key_exists('referred_amount', $item)) {
                        continue;
                    }

                    $expectedReferredAmount = round(((float) ($item['bill_amount'] ?? 0)) - ((float) ($item['system_amount'] ?? 0)), 2);

                    if (round((float) $item['referred_amount'], 2) !== $expectedReferredAmount) {
                        $validator->errors()->add("items.$index.referred_amount", 'The referred amount must equal bill amount minus system amount.');
                    }
                }
            },
        ];
    }
}
