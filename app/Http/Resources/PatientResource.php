<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
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
            'age' => $this->age,
            'address' => $this->address,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'gender' => $this->gender,
        ];
    }
}
