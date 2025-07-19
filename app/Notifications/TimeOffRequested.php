<?php

namespace App\Notifications;

use App\Models\TimeOffRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class TimeOffRequested extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $timeOff;
    protected $managerUserId;

    public function __construct(TimeOffRequest $timeOff, $managerUserId)
    {
        $this->timeOff = $timeOff;
        $this->managerUserId = $managerUserId;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

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

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Time-Off Request',
            'message' => $this->timeOff->employee->first_name . ' requested time off from ' .
                $this->timeOff->start_date . ' to ' . $this->timeOff->end_date,
            'employee_id' => $this->timeOff->employee_id,
            'time_off_request_id' => $this->timeOff->id,
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.' . $this->managerUserId);
    }

    public function broadcastAs(): string
    {
        return 'TimeOffRequested';
    }
}
