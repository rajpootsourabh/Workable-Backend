<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            // 'id' => $this->id,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
        ];
    }
}
