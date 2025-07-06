<?php

namespace App\Notifications;

use App\Models\TimeOffRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TimeOffRequested extends Notification
{
    use Queueable;

    protected $timeOff;

    /**
     * Create a new notification instance.
     */
    public function __construct(TimeOffRequest $timeOff)
    {
        $this->timeOff = $timeOff;
    }

    /**
     * Determine which channels to send the notification on.
     */
    public function via($notifiable): array
    {
        return ['database']; // ✅ Only database channel
    }

    /**
     * Get the array data stored in the database.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'New Time-Off Request',
            'message' => $this->timeOff->employee->first_name . ' requested time off from ' .
                $this->timeOff->start_date . ' to ' . $this->timeOff->end_date,
            'employee_id' => $this->timeOff->employee_id,
            'time_off_request_id' => $this->timeOff->id,
        ];
    }
}
