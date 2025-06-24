<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // <-- أضف هذا السطر
use App\Http\View\Composers\NotificationComposer; // <-- أضف هذا السطر
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    // ...

    public function boot(): void
{
    // جلب الإشعارات لكل الواجهات التي تستخدم 'layouts.app'
    View::composer('layouts.app', function ($view) {
        if (Auth::check()) {
            $user = Auth::user();
            $view->with([
                // جلب آخر 5 إشعارات فقط للعرض في القائمة المنسدلة
                'user_notifications' => $user->notifications()->limit(5)->get(),
                // جلب عدد الإشعارات غير المقروءة فقط لعرضه على الجرس
                'user_notifications_count' => $user->unreadNotifications()->count(),
            ]);
        }
    });
}
}