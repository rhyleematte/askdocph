<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'guidelines',
        'cover_photo',
        'creator_id',
        'visibility',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class , 'creator_id');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->with(['likes', 'comments', 'media'])->latest();
    }

    public function allComments()
    {
        return $this->hasManyThrough(PostComment::class , Post::class);
    }

    public function allLikes()
    {
        return $this->hasManyThrough(PostLike::class , Post::class);
    }

    public function getCoverUrlAttribute(): string
    {
        $photo = $this->cover_photo;

        if (!$photo) {
            return asset('assets/img/defaultcover.png');
        }

        if (strpos($photo, 'http') === 0) {
            return $photo;
        }

        return asset('storage/' . ltrim($photo, '/'));
    }

    public function getActivityLevelAttribute()
    {
        $newMembers = $this->recent_members_count ?? 0;
        $newPosts = $this->recent_posts_count ?? 0;
        $newComments = $this->recent_comments_count ?? 0;
        $newLikes = $this->recent_likes_count ?? 0;

        // Base activity on recent actions (last 30 days)
        $score = ($newPosts * 5) + ($newMembers * 3) + ($newComments * 2) + $newLikes;

        if ($score >= 50)
            return 'Very Active';
        if ($score >= 20)
            return 'Active';
        if ($score >= 5)
            return 'Moderate';
        return 'Quiet';
    }
}
