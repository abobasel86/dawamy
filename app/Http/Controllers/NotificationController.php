<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display a paginated list of all notifications for the user.
     * The key change is here: we NO LONGER mark all as read automatically.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read and redirect to its associated URL.
     * This will be used for all notifications EXCEPT the "delegation" ones.
     */
    public function readAndRedirect(DatabaseNotification $notification)
    {
        if (Auth::id() !== $notification->notifiable_id) {
            abort(403, 'Unauthorized');
        }
        
        $notification->markAsRead();

        $url = data_get($notification, 'data.url', route('dashboard'));
        
        return redirect($url);
    }

    /**
     * Fetch a summary of the user's notifications for the dropdown.
     */
    public function summary()
    {
        try {
            $user = Auth::user();

            $notifications = $user->notifications()->latest()->take(5)->get();
            $totalUnread = $user->unreadNotifications()->count();

            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'latest_notifications' => $formattedNotifications,
                'total_unread' => $totalUnread,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notification summary: ' . $e->getMessage());
            return response()->json(['error' => 'Could not fetch notifications summary.'], 500);
        }
    }

    /**
     * Mark a specific notification as read via an API call (for dropdown and delegation notifications).
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        if (Auth::id() === $notification->notifiable_id) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}