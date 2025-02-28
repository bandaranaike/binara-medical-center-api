<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillCrudResource extends JsonResource
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
            "doctor" => $this->doctor?->name,
            "patient" => $this->patient->name,
            "payment" => $this->system_amount + $this->bill_amount,
            "appointment_date" => Carbon::create($this->date)->format('Y-m-d'),
            "payment_type" => $this->payment_type,
            "created_at" => $this->created_at->format('Y-m-d H:i:s'),
            "appointment_type" => $this->appointment_type,
            "payment_status" => $this->payment_status,
            "status" => $this->status
        ];
    }
}
