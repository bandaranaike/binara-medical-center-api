<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "brand_id" => "required|integer|exists:brands,id",
            "supplier_id" => "required|integer|exists:suppliers,id",
            "unit_price" => "required|numeric",
            "batch_number" => "nullable|string",
            "initial_quantity" => "required|numeric",
            "quantity" => "required|numeric",
            "expire_date" => "required|date",
            "cost" => "nullable|numeric",
        ];
    }
}
