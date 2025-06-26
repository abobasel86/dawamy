<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveFinalDecision extends Notification implements ShouldQueue
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

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }
    
    public function toWebPush($notifiable, $notification)
    {
        $isApproved = $this->leaveRequest->status === 'approved';
        $title = $isApproved ? 'تمت الموافقة على طلب الإجازة' : 'تم رفض طلب الإجازة';
        $startDate = Carbon::parse($this->leaveRequest->start_date)->format('d/m/Y');
        $body = $isApproved
            ? "تهانينا! تمت الموافقة على طلب إجازتك الذي يبدأ بتاريخ {$startDate}."
            : "نأسف لإبلاغك بأنه تم رفض طلب إجازتك الذي يبدأ بتاريخ {$startDate}.";

        return (new WebPushMessage)
            ->title($title)
            ->icon('/images/icons/icon-192x192.png')
            ->body($body)
            ->data(['url' => $this->url]);
    }

    public function toArray(object $notifiable): array
    {
        $status_text = $this->leaveRequest->status === 'approved' ? 'الموافقة على' : 'رفض';
        $message = 'تم ' . $status_text . ' طلب الإجازة الذي قدمته بتاريخ ' . Carbon::parse($this->leaveRequest->start_date)->format('d/m/Y');

        return [
            'message' => $message,
            'leave_request_id' => $this->leaveRequest->id,
            'url' => $this->url
        ];
    }
}
