<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display the user's notifications.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        $query = auth()->user()->notifications()->recent();
        
        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }
        
        $notifications = $query->paginate(20);
        
        return view('notifications.index', compact('notifications', 'filter'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        
        $notification->markAsRead();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }
        
        return back()->with('status', 'Notification marked as read.');
    }

    /**
     * Mark a notification as unread.
     */
    public function markAsUnread(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        
        $notification->markAsUnread();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('status', 'Notification marked as unread.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->notifications()->unread()->update(['read_at' => now()]);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('status', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        
        $notification->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('status', 'Notification deleted.');
    }

    /**
     * Get unread notifications count (for AJAX requests).
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unread_notifications_count,
        ]);
    }

    /**
     * Get recent notifications (for AJAX requests).
     */
    public function recent()
    {
        $notifications = auth()->user()
            ->notifications()
            ->recent()
            ->limit(10)
            ->get();
            
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => auth()->user()->unread_notifications_count,
        ]);
    }
}
