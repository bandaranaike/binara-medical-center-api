<?php

namespace App\Http\Requests;

use App\Models\Holiday;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateHolidayRequest extends FormRequest
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
        $holidayId = (int) $this->route('holiday');

        return [
            'name' => 'required|string|max:150',
            'date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($holidayId): void {
                    if (Holiday::query()
                        ->whereKeyNot($holidayId)
                        ->whereDate('date', (string) $value)
                        ->exists()) {
                        $fail("The {$attribute} has already been taken.");
                    }
                },
            ],
            'message' => 'nullable|string|max:255',
            'is_closed' => 'required|boolean',
        ];
    }
}
