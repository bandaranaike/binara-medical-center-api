<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'bill_price' => $this->bill_price,
            'system_price' => $this->system_price,
            'is_percentage' => $this->is_percentage,
            'separate_items' => $this->separate_items,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
