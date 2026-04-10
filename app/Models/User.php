<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'username',
        'fname',
        'mname',
        'lname',
        'gender',
        'bday',
        'role',
        'doctor_status',
        'profile_photo',
        'cover_photo',
        'bio',
        'is_online',
        'is_free_to_talk',
        'allow_ai_recommendation',
        'last_active_at',
        'messenger_active_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'last_active_at'          => 'datetime',
        'messenger_active_status' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function posts()
    {
        return $this->hasMany(Post::class)->with(['likes', 'comments', 'media'])->latest();
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function groups()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function joinedResources()
    {
        return $this->belongsToMany(Resource::class, 'resource_user')
            ->withTimestamps()
            ->withPivot('status');
    }

    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_saves')
            ->withTimestamps();
    }

    public function doctorApplication()
    {
        return $this->hasOne(DoctorApplication::class)->latest();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_message_id', 'muted', 'archived', 'archived_at', 'last_read_at', 'deleted_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_user_id');
    }

    public function doctorSchedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }

    // ── Accessors ─────────────────────────────────────────────────
    public function getFullNameAttribute()
    {
        // Normalize each part: first letter uppercase, rest lowercase
        $parts = [];
        foreach (['fname', 'mname', 'lname'] as $field) {
            $val = $this->{ $field};
            if ($val && trim($val) !== '') {
                $parts[] = $this->toTitleCase(trim($val));
            }
        }
        return implode(' ', $parts);
    }

    public function getShortNameAttribute()
    {
        $parts = [];

        if ($this->fname && trim($this->fname) !== '') {
            $fnameParts = explode(' ', trim($this->fname));
            $parts[] = $this->toTitleCase($fnameParts[0]);
        }

        if ($this->mname && trim($this->mname) !== '') {
            $initial = mb_substr(trim($this->mname), 0, 1, 'UTF-8');
            $parts[] = mb_strtoupper($initial, 'UTF-8') . '.';
        }

        if ($this->lname && trim($this->lname) !== '') {
            $parts[] = $this->toTitleCase(trim($this->lname));
        }

        return implode(' ', $parts);
    }

    public function isApprovedDoctor()
    {
        return $this->doctor_status === 'approved';
    }

    /**
     * A user is considered "online" if they made a request within the last 5 minutes.
     * This replaces the manual is_online toggle for messenger presence.
     */
    public function isOnline(): bool
    {
        // If "Active Status" is OFF, always return false (Stealth Mode)
        if (!$this->messenger_active_status) {
            return false;
        }

        return $this->last_active_at !== null
            && $this->last_active_at->gt(now()->subMinutes(5));
    }

    public function getProfessionalTitleAttribute()
    {
        if (!$this->isApprovedDoctor()) return null;
        $app = $this->doctorApplication;
        return $app ? $app->professional_titles : null;
    }

    /**
     * Converts a string to Title Case — each word's first letter
     * uppercase, the rest lowercase. Handles compound names with hyphens.
     */
    private function toTitleCase(string $str)
    {
        // mb_convert_case handles Unicode (e.g. Filipino names)
        return mb_convert_case(mb_strtolower($str), MB_CASE_TITLE, 'UTF-8');
    }

    public function getAvatarUrlAttribute()
    {
        $photo = $this->profile_photo;

        if (!$photo || $photo === 'profiles/default.png') {
            return asset('assets/img/default.png');
        }

        if (strpos($photo, 'http') === 0) {
            return $photo;
        }

        return asset('storage/' . ltrim($photo, '/'));
    }

    public function getCoverUrlAttribute()
    {
        $photo = $this->cover_photo;

        if (!$photo) {
            // Return a 1x1 transparent GIF data URI instead of a missing file to prevent 404 errors
            return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
        }

        if (strpos($photo, 'http') === 0) {
            return $photo;
        }

        return asset('storage/' . ltrim($photo, '/'));
    }
}
