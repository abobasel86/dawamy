<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveRequest;

class LeaveRequestDecision extends Notification
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
        $status_text = $this->leaveRequest->status === 'approved' ? 'الموافقة على' : 'رفض';
        $message = 'تم ' . $status_text . ' طلب الإجازة الذي قدمته بتاريخ ' . $this->leaveRequest->start_date;

        return [
            'message' => $message,
            'route_name' => 'leaves.index',
            'leave_request_id' => $this->leaveRequest->id,
        ];
    }
}