<?php

namespace App\Notifications;

use App\Models\TimeOffRequest;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class TimeOffStatusChanged extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $timeOff;
    protected $status;

    public function __construct(TimeOffRequest $timeOff, string $status)
    {
        $this->timeOff = $timeOff;
        $this->status = $status;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Time-Off ' . ucfirst($this->status),
            'message' => 'Your time-off request from ' . $this->timeOff->start_date .
                ' to ' . $this->timeOff->end_date . ' has been ' . $this->status . '.',
            'time_off_request_id' => $this->timeOff->id,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => uniqid(),
            'title' => 'Time-Off ' . ucfirst($this->status),
            'message' => 'Your time-off request from ' . $this->timeOff->start_date .
                ' to ' . $this->timeOff->end_date . ' has been ' . $this->status . '.',
            'time_off_request_id' => $this->timeOff->id,
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    public function broadcastOn(): PrivateChannel

    {
        // Log::info('Broadcasting to: App.Models.User.' . $this->timeOff->employee->user->id);
        return new PrivateChannel("App.Models.User.{$this->timeOff->employee->user->id}");
    }

    public function broadcastAs(): string
    {
        return 'TimeOffStatusChanged';
    }
}
