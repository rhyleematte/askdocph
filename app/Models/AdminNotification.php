<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdminNotification extends Model
{
    protected $fillable = ['type', 'data'];
    protected $casts = ['data' => 'array'];

    /**
     * Create a notification visible to all admins.
     */
    public static function createForAll(string $type, array $data): self
    {
        return self::create(['type' => $type, 'data' => $data]);
    }

    /**
     * Check if a specific admin has read this notification.
     */
    public function isReadBy(int $adminId): bool
    {
        return $this->reads()->where('admin_id', $adminId)->exists();
    }

    public function reads()
    {
        return $this->hasMany(AdminNotificationRead::class, 'admin_notification_id');
    }

    /**
     * Count unread notifications for the current admin.
     */
    public static function unreadCountFor(int $adminId): int
    {
        return static::whereDoesntHave('reads', function ($q) use ($adminId) { $q->where('admin_id', $adminId); })->count();
    }
}
