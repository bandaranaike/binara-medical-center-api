<?php

namespace App\Http\Requests;

use App\Models\Bill;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateBillRequest extends FormRequest
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
        $statusArray = [
            Bill::STATUS_BOOKED,
            Bill::STATUS_DOCTOR,
            Bill::STATUS_DONE,
            Bill::STATUS_PHARMACY,
            Bill::STATUS_RECEPTION,
            Bill::STATUS_TREATMENT,
        ];
        return [
            'status' => 'required|string|max:255|in:' . implode(',', $statusArray),
        ];
    }
}
