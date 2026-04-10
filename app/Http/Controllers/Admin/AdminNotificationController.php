<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\AdminNotificationRead;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    /** Retrieves latest notifications for the dropdown */
    public function apiIndex()
    {
        $me = Auth::guard('admin')->user();
        $notifications = AdminNotification::latest()->take(20)->get()->map(function($n) use ($me) {
            $isRead = $n->isReadBy($me->id);
            return [
                'id' => $n->id,
                'is_read' => $isRead,
                'url' => route('admin.applications.show', $n->data['application_id'] ?? 0),
                'message' => $n->data['message'] ?? 'New Notification',
                'created_at' => $n->created_at->diffForHumans(),
                'actor' => [
                    'name' => 'Doctor Profile', // It's usually a doctor applying
                    'avatar_url' => asset('assets/img/default.png')
                ]
            ];
        });

        return response()->json([
            'ok' => true,
            'unread_count' => AdminNotification::unreadCountFor($me->id),
            'notifications' => $notifications
        ]);
    }

    /** Mark all notifications as read for this admin */
    public function apiReadAll()
    {
        $me = Auth::guard('admin')->user();
        $notifications = AdminNotification::all();
        foreach ($notifications as $n) {
            if (!$n->isReadBy($me->id)) {
                AdminNotificationRead::firstOrCreate([
                    'admin_notification_id' => $n->id,
                    'admin_id'              => $me->id,
                ], ['read_at' => now()]);
            }
        }
        return response()->json(['ok' => true]);
    }

    /** Mark a specific notification as read */
    public function apiRead($id)
    {
        $me = Auth::guard('admin')->user();
        AdminNotificationRead::firstOrCreate([
            'admin_notification_id' => $id,
            'admin_id'              => $me->id,
        ], ['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
