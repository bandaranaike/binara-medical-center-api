<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
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
            "brand" => $this->brand->name,
            "supplier" => $this->supplier->name,
            "unit_price" => $this->unit_price,
            "batch_number" => $this->batch_number,
            "quantity" => $this->quantity,
            "expire_date" => $this->expire_date,
            "cost" => $this->cost
        ];
    }
}
