<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ExperienceDetailResource extends JsonResource
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
        'education' => $this->education,
        'job' => $this->job,
        'skill' => $this->skill,
        'language' => $this->language,
        'resume' => $this->resume ? asset(Storage::url($this->resume)) : null,
    ];
}

}
