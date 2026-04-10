<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\ConversationParticipant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function getConversations()
    {
        $user = Auth::user();
        
        // 1. Get existing conversations (not deleted for this user)
        $query = $user->conversations()
            ->with(['users', 'latestMessage'])
            ->wherePivot('deleted_at', null);

        // Filter by archived status
        if (request()->query('archived') == '1') {
            $query->wherePivotNotNull('archived_at');
        } else {
            $query->wherePivot('archived_at', null);
        }

        $conversations = $query->orderBy('conversations.updated_at', 'desc')->get();

        // 2. Filter conversations to keep only one per user pair
        $alreadyPaired = [];
        $conversations = $conversations->filter(function($conv) use ($user, &$alreadyPaired) {
            $otherUser = $conv->users->where('id', '!=', $user->id)->first();
            if (!$otherUser) return true; // Keep groups if they exist
            
            if (isset($alreadyPaired[$otherUser->id])) {
                return false; // Skip duplicate conversations with the same user
            }
            $alreadyPaired[$otherUser->id] = true;
            return true;
        });

        $existingConversationUserIds = $conversations->flatMap(function($conv) use ($user) {
            return $conv->users->pluck('id');
        })->unique()->toArray();

        // 2. Get mutual followers and followed users
        $followedIds = $user->following()->pluck('users.id')->toArray();
        $followerIds = $user->followers()->pluck('users.id')->toArray();
        $mutualIds = array_intersect($followedIds, $followerIds);
        
        // Users to show who don't have conversations yet
        $contactIds = array_unique(array_merge($mutualIds, $followedIds));
        $contacts = User::whereIn('id', $contactIds)
            ->whereNotIn('id', $existingConversationUserIds)
            ->get();

        $formattedConversations = $conversations->map(function ($conv) use ($user) {
            $otherUser = $conv->users->where('id', '!=', $user->id)->first();
            
            $unreadCount = Message::where('conversation_id', $conv->id)
                ->where('sender_user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();

            return [
                'id' => $conv->id,
                'other_user' => $otherUser ? [
                    'id' => $otherUser->id,
                    'name' => $otherUser->full_name,
                    'avatar' => $otherUser->avatar_url,
                    'is_doctor' => $otherUser->isApprovedDoctor(),
                    'is_online' => $otherUser->isOnline(),
                ] : null,
                'latest_message' => $conv->latestMessage,
                'unread_count' => $unreadCount,
                'is_conversation' => true
            ];
        });

        $formattedContacts = $contacts->map(function ($u) use ($mutualIds) {
            return [
                'id' => null, // No conversation yet
                'other_user' => [
                    'id' => $u->id,
                    'name' => $u->full_name,
                    'avatar' => $u->avatar_url,
                    'is_doctor' => $u->isApprovedDoctor(),
                    'is_mutual' => in_array($u->id, $mutualIds),
                    'is_online' => $u->isOnline(),
                ],
                'latest_message' => null,
                'unread_count' => 0,
                'is_conversation' => false
            ];
        });

        // Combine and sort: Conversations with messages first, then mutuals, then following
        $all = $formattedConversations->concat($formattedContacts);
        
        $sorted = $all->sort(function($a, $b) {
            // Conversations with latest message first
            if ($a['latest_message'] && !$b['latest_message']) return -1;
            if (!$a['latest_message'] && $b['latest_message']) return 1;
            
            // Then conversations without messages
            if ($a['is_conversation'] && !$b['is_conversation']) return -1;
            if (!$a['is_conversation'] && $b['is_conversation']) return 1;

            // Then mutuals
            $aMutual = $a['other_user']['is_mutual'] ?? false;
            $bMutual = $b['other_user']['is_mutual'] ?? false;
            if ($aMutual && !$bMutual) return -1;
            if (!$aMutual && $bMutual) return 1;

            return 0;
        })->values();

        return response()->json($sorted);
    }

    public function deleteConversation($id)
    {
        $user = Auth::user();
        
        DB::table('conversation_participants')
            ->where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->update(['deleted_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function getMessages($conversationId, Request $request)
    {
        $afterId = $request->get('after_id');
        $user = Auth::user();
        
        $query = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc');

        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->get();

        // Mark incoming messages as read
        Message::where('conversation_id', $conversationId)
            ->where('sender_user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:conversations,id',
            'receiver_id' => 'nullable|exists:users,id',
            'body' => 'required|string',
        ]);

        $user = Auth::user();
        $senderId = $user->id;
        $conversationId = $request->conversation_id;

        if (!$conversationId && $request->receiver_id) {
            // Check for existing direct conversation
            $conversation = Conversation::with(['participants', 'latestMessage'])
            ->whereHas('participants', function($q) use ($user) {
                $q->where('conversation_participants.user_id', $user->id)
                  ->whereNull('conversation_participants.deleted_at');
            })
                ->whereHas('participants', function($q) use ($request) {
                    $q->where('user_id', $request->receiver_id);
                })
                ->latest('conversations.updated_at')
                ->first();

            if (!$conversation) {
                // Create new conversation
                $conversation = Conversation::create(['type' => 'direct']);
                ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $senderId]);
                ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $request->receiver_id]);
            }
            $conversationId = $conversation->id;
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_user_id' => $senderId,
            'body' => $request->body,
            'message_type' => 'text',
        ]);

        // Touch conversation to update updated_at
        Conversation::where('id', $conversationId)->update(['updated_at' => now()]);

        // Restore conversation visibility for everyone in the conversation
        DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->update(['deleted_at' => null]);

        return response()->json($message->load('sender'));
    }

    public function setTyping(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required',
            'is_typing' => 'required|boolean'
        ]);

        $userId = Auth::id();
        $convId = $request->conversation_id;
        $key = "typing.{$convId}.{$userId}";

        if ($request->is_typing) {
            Cache::put($key, true, now()->addSeconds(5));
        } else {
            Cache::forget($key);
        }

        return response()->json(['ok' => true]);
    }

    public function getTyping(Request $request, $conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::with('users')->findOrFail($conversationId);
        
        $otherUser = $conversation->users->where('id', '!=', $user->id)->first();
        
        if (!$otherUser) return response()->json(['is_typing' => false]);

        $isTyping = Cache::has("typing.{$conversationId}.{$otherUser->id}");

        return response()->json(['is_typing' => $isTyping]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $currentUser = Auth::user();
        $followedIds = $currentUser->following()->pluck('users.id')->toArray();
        $followerIds = $currentUser->followers()->pluck('users.id')->toArray();
        $mutualIds = array_intersect($followedIds, $followerIds);

        $users = User::where('id', '!=', $currentUser->id)
            ->where(function($q) use ($query) {
                $q->where('fname', 'LIKE', "%{$query}%")
                  ->orWhere('lname', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%");
            })
            ->get()
            ->map(function($u) use ($mutualIds, $followedIds) {
                $priority = 3; // Default
                if (in_array($u->id, $mutualIds)) {
                    $priority = 1; // Mutual
                } elseif (in_array($u->id, $followedIds)) {
                    $priority = 2; // Following only
                }

                return [
                    'id' => $u->id,
                    'name' => $u->full_name,
                    'avatar' => $u->avatar_url,
                    'is_doctor' => $u->isApprovedDoctor(),
                    'priority' => $priority
                ];
            })
            ->sortBy('priority')
            ->values()
            ->take(10);

        return response()->json($users);
    }
    public function unreadCounts()
    {
        $user = Auth::user();
        
        $notifCount = \App\Models\Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
            
        // Use EXACT same logic as getConversations to ensure badge matches drawer
        $conversations = $user->conversations()
            ->with(['users'])
            ->wherePivot('deleted_at', null)
            ->orderBy('conversations.updated_at', 'desc')
            ->get();

        $alreadyPaired = [];
        $totalMsgCount = 0;

        foreach ($conversations as $conv) {
            $otherUser = $conv->users->where('id', '!=', $user->id)->first();
            
            // Mirror drawers visibility rules
            if (!$otherUser) continue; 
            
            // Deduplication: Only count the same person once (the most recent one)
            if (isset($alreadyPaired[$otherUser->id])) continue;
            $alreadyPaired[$otherUser->id] = true;

            // Add up unread messages for this visible conversation
            $totalMsgCount += $conv->messages()
                ->where('sender_user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();
        }
            
        return response()->json([
            'notifications' => $notifCount,
            'messages' => $totalMsgCount
        ]);
    }

    public function toggleActiveStatus(Request $request)
    {
        $user = Auth::user();
        $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN);
        
        $user->update(['messenger_active_status' => $status]);

        return response()->json([
            'success' => true,
            'messenger_active_status' => $user->messenger_active_status,
            'is_online' => $user->isOnline() // Should be false if status was just set to false
        ]);
    }

    public function archiveConversation(Request $request, $id)
    {
        $user = Auth::user();
        DB::table('conversation_participants')
            ->where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->update(['archived_at' => now(), 'archived' => true]);

        return response()->json(['success' => true]);
    }

    public function unarchiveConversation(Request $request, $id)
    {
        $user = Auth::user();
        DB::table('conversation_participants')
            ->where('conversation_id', $id)
            ->where('user_id', $user->id)
            ->update(['archived_at' => null, 'archived' => false]);

        return response()->json(['success' => true]);
    }
}
