<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'preferred_name' => $this->preferred_name,
            'country' => $this->country,
            'address' => $this->address,
            'social_media' => $this->social_media,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate,
            'marital_status' => $this->marital_status,
            'phone' => $this->phone,
            'work_email' => $this->work_email,
            'personal_email' => $this->personal_email,
            'chat_video_call' => $this->chat_video_call,
            'profile_image' => $this->generateFileUrl($this->profile_image),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include nested resources
            'company' => new CompanyResource($this->whenLoaded('company')),
            'job_detail' => new JobDetailResource($this->whenLoaded('jobDetail')),
            'compensation_detail' => new CompensationDetailResource($this->whenLoaded('compensationDetail')),
            'legal_document' => new LegalDocumentResource($this->whenLoaded('legalDocument')),
            'experience_detail' => new ExperienceDetailResource($this->whenLoaded('experienceDetail')),
            'emergency_contact' => new EmergencyContactResource($this->whenLoaded('emergencyContact')),
        ];
    }

    protected function generateFileUrl(?string $path): ?string
    {
        if (!$path) return null;

        // Encode the path properly
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));

        return url("api/v.1/files/{$encodedPath}");
    }
}
