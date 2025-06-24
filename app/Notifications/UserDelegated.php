<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class UserDelegated extends Notification
{
    use Queueable;

    protected $leaveRequest;

    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $startDate = Carbon::parse($this->leaveRequest->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($this->leaveRequest->end_date)->format('Y-m-d');
        
        return [
            'message' => 'قام ' . $this->leaveRequest->user->name . ' بتفويضك للقيام بمهامه أثناء إجازته من ' . $startDate . ' إلى ' . $endDate,
            'route_name' => 'dashboard',
            'leave_request_id' => $this->leaveRequest->id,
        ];
    }
}