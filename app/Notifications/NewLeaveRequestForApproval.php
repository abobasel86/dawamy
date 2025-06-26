<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\LeaveRequest;

class NewLeaveRequestForApproval extends Notification implements ShouldQueue
{
    use Queueable;

    protected $leaveRequest;
    protected $url; // <-- سنقوم بتخزين الرابط هنا

    // تعديل الـ constructor ليقبل الرابط
    public function __construct(LeaveRequest $leaveRequest, string $url)
    {
        $this->leaveRequest = $leaveRequest;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['database', WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $message = 'لديك طلب إجازة جديد من الموظف: ' . $this->leaveRequest->user->name;
        return (new WebPushMessage)
            ->title('طلب إجازة جديد')
            ->icon('/images/icons/icon-192x192.png')
            ->body($message)
            // استخدام الرابط الجاهز
            ->data(['url' => $this->url]);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'لديك طلب إجازة جديد من الموظف: ' . $this->leaveRequest->user->name,
            'leave_request_id' => $this->leaveRequest->id,
            // استخدام الرابط الجاهز
            'url' => $this->url
        ];
    }
}
