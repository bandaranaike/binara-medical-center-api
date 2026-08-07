<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\AppointmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePublicServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:40'],
            'type' => ['nullable', 'string', Rule::in([...AppointmentType::toArray(), 'others'])],
            'bill_price' => ['required', 'numeric', 'min:0'],
            'system_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'key' => is_string($this->input('key')) ? Str::slug($this->input('key')) : $this->input('key'),
        ]);
    }
}
