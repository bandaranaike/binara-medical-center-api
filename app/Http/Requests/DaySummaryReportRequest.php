<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DaySummaryReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', Rule::date()->format('Y-m-d')],
            'shift' => ['required', 'string', 'in:morning,evening'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date' => $this->input('date', now()->toDateString()),
        ]);
    }
}
