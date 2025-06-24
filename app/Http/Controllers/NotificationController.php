<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * عرض صفحة كل إشعارات المستخدم مع ترقيم الصفحات
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * تعليم إشعار واحد كمقروء ثم إعادة التوجيه للرابط الخاص به
     */
    public function readAndRedirect(DatabaseNotification $notification)
{
    // تأكيد ملكية الإشعار للمستخدم الحالي
    if (Auth::id() === $notification->notifiable_id) {
        $notification->markAsRead();
    }

    // التوجيه حسب route_name المخزن في بيانات الإشعار
    $routeName = data_get($notification, 'data.route_name');
    
    if ($routeName && \Route::has($routeName)) {
        return redirect()->route($routeName);
    }

    return redirect()->route('dashboard');
}
}