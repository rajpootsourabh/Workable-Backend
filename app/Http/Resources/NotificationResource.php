<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notifiable_id' => $this->notifiable_id,
            'title' => $this->data['title'] ?? '',
            'message' => $this->data['message'] ?? '',
            'employee_id' => $this->data['employee_id'] ?? null,
            'employee_name' => $this->data['employee_name'] ?? null,
            'time_off_request_id' => $this->data['time_off_request_id'] ?? null,
            'status' => $this->data['status'] ?? null,
            'start_date' => $this->data['start_date'] ?? null,
            'end_date' => $this->data['end_date'] ?? null,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
