<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $notifications = Auth::user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * تعليم إشعار واحد كمقروء ثم إعادة التوجيه للرابط الخاص به
     */
    public function readAndRedirect(DatabaseNotification $notification)
    {
        if (Auth::id() !== $notification->notifiable_id) {
            abort(403);
        }
        
        $notification->markAsRead();

        // === START: التصحيح هنا ===
        // الاعتماد على حقل url الموحّد بدلاً من route_name
        $url = data_get($notification, 'data.url', route('dashboard'));
        
        return redirect($url);
        // === END: التصحيح هنا ===
    }

    public function summary()
    {
        $user = Auth::user();
        return response()->json([
            'total_unread' => $user->unreadNotifications()->count(),
            'latest_notifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        if ($notification->notifiable_id === Auth::id()) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 403);
    }
}
