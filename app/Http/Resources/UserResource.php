<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // 'first_name' => $this->first_name,
            // 'last_name' => $this->last_name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'company' => [
                // 'id' => $this->company->id,
                'name' => $this->company->name,
                'website' => $this->company->website,
                'size' => $this->company->size,
                'phone_number' => $this->company->phone_number,
                'evaluating_website' => $this->company->evaluating_website,
                'company_logo' => $this->company->company_logo ? generateFileUrl($this->company->company_logo) : null,
                'company_description' => $this->company->company_description,
                
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
