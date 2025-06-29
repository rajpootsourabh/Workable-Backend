<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobDetailResource extends JsonResource
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
            'job_title' => $this->job_title,
            'hire_date' => $this->hire_date,
            'start_date' => $this->start_date,
            'entity' => $this->entity,
            'department' => $this->department,
            'division' => $this->division,
            'manager' => $this->manager ? [
                'id'   => $this->manager_id,
                'name' => $this->manager->first_name . ' ' . $this->manager->last_name,
            ] : null,
            'effective_date' => $this->effective_date,
            'employment_type' => $this->employment_type,
            'workplace' => $this->workplace,
            'expiry_date' => $this->expiry_date,
            'note' => $this->note,
            'work_schedule' => $this->work_schedule,
        ];
    }
}
