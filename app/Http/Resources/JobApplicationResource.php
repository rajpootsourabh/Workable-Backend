<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
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
            // Candidate data
            'candidate' => [
                'id' => $this->candidate->id,
                'first_name' => $this->candidate->first_name,
                'last_name' => $this->candidate->last_name,
                'designation' => $this->candidate->designation,
                'experience' => $this->candidate->experience,
                'phone' => $this->candidate->phone,
                'location' => $this->candidate->location,
                'current_ctc' => $this->candidate->current_ctc,
                'expected_ctc' => $this->candidate->expected_ctc,
                'profile_pic' => $this->candidate->profile_pic ? asset('storage/' . $this->candidate->profile_pic) : null,
                'resume' => $this->candidate->resume ? asset('storage/' . $this->candidate->resume) : null,
            ],
            // Application data
            'application' => [
                'id' => $this->id,
                'job_post_id' => $this->job_post_id,
                'status' => $this->status,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ],
        ];
    }
}
