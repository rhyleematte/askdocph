<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMessageController extends Controller
{
    /** API: List all other admins with last message + unread count */
    public function apiConversations()
    {
        $me = Auth::guard('admin')->user();
        $admins = Admin::where('id', '!=', $me->id)->get()->map(function ($admin) use ($me) {
            $lastMsg = AdminMessage::where(function ($q) use ($me, $admin) {
                    $q->where('from_admin_id', $me->id)->where('to_admin_id', $admin->id);
                })->orWhere(function ($q) use ($me, $admin) {
                    $q->where('from_admin_id', $admin->id)->where('to_admin_id', $me->id);
                })->latest()->first();

            $unread = AdminMessage::where('from_admin_id', $admin->id)
                ->where('to_admin_id', $me->id)
                ->whereNull('read_at')
                ->count();

            return [
                'id' => $admin->id,
                'is_conversation' => true,
                'other_user' => [
                    'id' => $admin->id,
                    'name' => $admin->short_name ?: $admin->email,
                    'avatar' => $admin->avatar_url ? asset('storage/' . $admin->avatar_url) : asset('assets/img/default.png'),
                    'is_doctor' => false,
                    'is_mutual' => true // Force true so css picks up the badge if we want
                ],
                'latest_message' => $lastMsg ? [
                    'body' => $lastMsg->body,
                    'created_at' => $lastMsg->created_at,
                    'is_unread' => $unread > 0
                ] : null,
                'unread_count' => $unread
            ];
        })->sortByDesc(function ($a) { 
            return $a['latest_message'] ? $a['latest_message']['created_at'] : '0000-00-00'; 
        })->values();

        return response()->json($admins);
    }

    /** API: Search admins */
    public function apiSearch(Request $request)
    {
        $q = $request->query('q');
        if (!$q) return response()->json([]);

        $me = Auth::guard('admin')->user();
        $admins = Admin::where('id', '!=', $me->id)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->take(10)
            ->get()->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $admin->short_name ?: $admin->email,
                    'avatar' => $admin->avatar_url ? asset('storage/' . $admin->avatar_url) : asset('assets/img/default.png'),
                    'is_doctor' => false,
                    'priority' => 1
                ];
            });

        return response()->json($admins);
    }

    /** API: Get messages for a thread (polling) */
    public function apiMessages(Request $request, $adminId)
    {
        $me = Auth::guard('admin')->user();
        $afterId = $request->query('after_id', 0);

        // Mark incoming as read
        AdminMessage::where('from_admin_id', $adminId)
            ->where('to_admin_id', $me->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = AdminMessage::where(function ($q) use ($me, $adminId) {
                $q->where('from_admin_id', $me->id)->where('to_admin_id', $adminId);
            })->orWhere(function ($q) use ($me, $adminId) {
                $q->where('from_admin_id', $adminId)->where('to_admin_id', $me->id);
            })
            ->where('id', '>', $afterId)
            ->orderBy('created_at')
            ->get()->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'conversation_id' => $msg->from_admin_id == Auth::guard('admin')->id() ? $msg->to_admin_id : $msg->from_admin_id, // Simulate conv ID using the other admin's ID
                    'sender_user_id' => $msg->from_admin_id,
                    'receiver_user_id' => $msg->to_admin_id,
                    'body' => $msg->body,
                    'created_at' => $msg->created_at
                ];
            });

        return response()->json($messages);
    }

    /** API: Send message */
    public function apiSend(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:admins,id',
            'body' => 'required|string|max:2000'
        ]);

        $me = Auth::guard('admin')->user();
        $msg = AdminMessage::create([
            'from_admin_id' => $me->id,
            'to_admin_id' => $request->receiver_id,
            'body' => $request->body
        ]);

        return response()->json([
            'id' => $msg->id,
            'conversation_id' => $msg->to_admin_id,
            'sender_user_id' => $msg->from_admin_id,
            'receiver_user_id' => $msg->to_admin_id,
            'body' => $msg->body,
            'created_at' => $msg->created_at
        ]);
    }
}
