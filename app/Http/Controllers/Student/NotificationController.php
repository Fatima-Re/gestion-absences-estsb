<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display student's notifications
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = Notification::where('user_id', Auth::id());
        
        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'read') {
                $query->read();
            } elseif ($request->status === 'unread') {
                $query->unread();
            }
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Order by latest
        $notifications = $query->latest()->paginate(20);
        
        return view('student.notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Verify ownership
        $this->authorizeNotification($notification);
        
        $notification->markAsRead();
        
        // If there's an action URL, redirect to it
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }
        
        return back()->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);
        
        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification)
    {
        // Verify ownership
        $this->authorizeNotification($notification);
        
        $notification->delete();
        
        return back()->with('success', 'Notification supprimée.');
    }

    /**
     * Delete all read notifications
     */
    public function clearRead()
    {
        Notification::where('user_id', Auth::id())
            ->read()
            ->delete();
        
        return back()->with('success', 'Notifications lues supprimées.');
    }

    /**
     * Get unread notifications count (for AJAX updates)
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->unread()
            ->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get latest notifications (for dropdown/sidebar)
     */
    public function latest()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->unread()
            ->latest()
            ->take(10)
            ->get();
        
        return response()->json($notifications);
    }

    /**
     * Show notification preferences form
     */
    public function preferences()
    {
        $student = Auth::user()->student;

        // Get current preferences
        $preferences = [
            'email_notifications' => true,
            'push_notifications' => true,
            'absence_alerts' => true,
            'justification_updates' => true,
            'session_cancellations' => true,
        ];

        // If stored in database, decode them
        // if ($student->notification_preferences) {
        //     $preferences = json_decode($student->notification_preferences, true);
        // }

        return view('student.notifications.preferences', compact('preferences'));
    }



    /**
     * Verify notification ownership
     */
    private function authorizeNotification(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Non autorisé.');
        }
    }
}
