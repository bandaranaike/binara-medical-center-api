<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "bill_id" => $this->bill_id,
            "bill_no" => $this->bill_id,
            "brand" => $this->brand->name,
            "brand_id" => $this->brand_id,
            "drug" => $this->brand->drug->name,
            "quantity" => $this->quantity,
            "total_price" => $this->total_price
        ];
    }
}
