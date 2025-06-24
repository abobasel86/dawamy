<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $notifications = $user->unreadNotifications()->take(5)->get();
            $view->with('user_notifications', $notifications);
        }
    }
}