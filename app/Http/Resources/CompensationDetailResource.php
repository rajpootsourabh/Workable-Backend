<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompensationDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'salary_details' => $this->salary_details,
            'bank_name' => $this->bank_name,
            'iban' => $this->iban,
            'account_number' => $this->account_number,
        ];
    }
}
