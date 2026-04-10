<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public static function create(User $recipient, ?User $actor, string $type, array $data = []): ?Notification
    {
        if ($actor && $recipient->id === $actor->id) {
            return null;
        }

        return Notification::create([
            'user_id' => $recipient->id,
            'actor_id' => $actor ? $actor->id : null,
            'type' => $type,
            'data' => $data,
        ]);
    }

    public static function notifyMentions(string $text, User $actor, array $data = []): void
    {
        $handles = self::extractMentions($text);
        if (empty($handles)) {
            return;
        }

        $handles = array_values(array_unique(array_map('strtolower', $handles)));
        if (empty($handles)) {
            return;
        }

        $users = User::whereIn(DB::raw('LOWER(username)'), $handles)->get();
        foreach ($users as $u) {
            if ($u->id === $actor->id) {
                continue;
            }
            self::create($u, $actor, 'mention', $data);
        }
    }

    private static function extractMentions(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        preg_match_all('/(?:^|\\s)@([A-Za-z0-9_.-]{2,32})/', $text, $matches);
        return $matches[1] ?? [];
    }
}






