<?php

namespace App\Notifications;

use App\Models\TimeOffRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TimeOffStatusChanged extends Notification
{
    use Queueable;

    protected $timeOff;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(TimeOffRequest $timeOff, string $status)
    {
        $this->timeOff = $timeOff;
        $this->status = $status;
    }

    /**
     * Notification channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Format for database notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Time-Off ' . ucfirst($this->status),
            'message' => 'Your time-off request from ' . $this->timeOff->start_date .
                ' to ' . $this->timeOff->end_date . ' has been ' . $this->status . '.',
            'employee_id' => $this->timeOff->employee_id,
            'time_off_request_id' => $this->timeOff->id,
        ];
    }
}
