<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveRequest;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class NewLeaveRequestForApproval extends Notification
{
    use Queueable;

    protected $leaveRequest;

    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
    }

    /**
     * تحديد القنوات التي سيتم إرسال الإشعار من خلالها
     */
    public function via(object $notifiable): array
    {
        // أرسل الإشعار لقاعدة البيانات (للعرض داخل التطبيق) وكـ Push Notification
        return ['database', WebPushChannel::class];
    }

    /**
     * تحديد البيانات التي سيتم تخزينها في قاعدة البيانات
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'لديك طلب إجازة جديد من الموظف: ' . $this->leaveRequest->user->name,
            'route_name' => 'manager.approvals.index', // رابط لصفحة الموافقات
            'leave_request_id' => $this->leaveRequest->id,
        ];
    }

    /**
     * تحديد شكل ومحتوى الـ Push Notification
     */
    public function toWebPush($notifiable, $notification)
    {
        $message = 'لديك طلب إجازة جديد من الموظف: ' . $this->leaveRequest->user->name;
        $url = route('manager.approvals.index');

        return (new WebPushMessage)
            ->title('طلب إجازة جديد')
            ->icon('/images/icons/icon-192x192.png') // يمكنك تغيير مسار الأيقونة
            ->body($message)
            ->action('عرض الطلبات', 'view_requests')
            ->data(['url' => $url]);
    }
}