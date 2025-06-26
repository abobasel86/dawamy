<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class UserDelegated extends Notification implements ShouldQueue
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
        $delegatorName = $this->leaveRequest->user->name;
        $startDate = Carbon::parse($this->leaveRequest->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($this->leaveRequest->end_date)->format('Y-m-d');

        return (new WebPushMessage)
            ->title('تم تفويضك بمهمة جديدة')
            ->icon('/images/icons/icon-192x192.png')
            ->body("لقد تم تفويضك من قبل الموظف {$delegatorName} لتغطية مهامه خلال فترة إجازته من {$startDate} إلى {$endDate}.")
            // استخدام الرابط الجاهز
            ->data(['url' => $this->url]);
    }

    public function toArray(object $notifiable): array
    {
        $startDate = Carbon::parse($this->leaveRequest->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($this->leaveRequest->end_date)->format('Y-m-d');

        return [
            'message' => 'قام ' . $this->leaveRequest->user->name . ' بتفويضك للقيام بمهامه أثناء إجازته من ' . $startDate . ' إلى ' . $endDate,
            'leave_request_id' => $this->leaveRequest->id,
            // استخدام الرابط الجاهز
            'url' => $this->url
        ];
    }
}
