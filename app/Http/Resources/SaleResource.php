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
            "brand" => $this->brand->name,
            "drug" => $this->brand->drug->name,
            "quantity" => $this->quantity,
            "total_price" => $this->total_price
        ];
    }
}
