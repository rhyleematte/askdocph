<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $limit = (int) $request->query('limit', 20);
        $limit = max(1, min($limit, 50));

        $notifications = Notification::with('actor')
            ->where('user_id', $user->id)
            ->latest()
            ->take($limit)
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $payload = $notifications->map(function ($n) {
            $actor = $n->actor;
            return [
                'id' => $n->id,
                'type' => $n->type,
                'message' => $n->data['message'] ?? '',
                'url' => $n->data['url'] ?? null,
                'data' => $n->data,
                'is_read' => $n->read_at !== null,
                'created_at' => $n->created_at ? $n->created_at->diffForHumans() : '',
                'actor' => $actor ? [
                    'id' => $actor->id,
                    'name' => $actor->short_name ?: $actor->full_name,
                    'username' => $actor->username,
                    'avatar_url' => $actor->avatar_url,
                    'profile_url' => route('profile.show', $actor->id),
                ] : null,
            ];
        });

        return response()->json([
            'ok' => true,
            'unread_count' => $unreadCount,
            'notifications' => $payload,
        ]);
    }

    public function markRead(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
