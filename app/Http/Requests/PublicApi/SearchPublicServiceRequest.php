<?php

namespace App\Http\Requests\PublicApi;

use App\Enums\AppointmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPublicServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['nullable', 'string', Rule::in([...AppointmentType::toArray(), 'others'])],
        ];
    }
}
