<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'resource_id',
        'shared_post_id',
        'post_type',
        'text_content',
        'mood',
        'hashtags',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    /** Return hashtags as a clean array of strings (without the #). */
    public function getHashtagsArrayAttribute(): array
    {
        if (!$this->hashtags)
            return [];
        return array_values(array_filter(
            array_map('trim', explode(',', $this->hashtags))
        ));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class)->whereNull('parent_comment_id')->with('user', 'replies.user')->latest();
    }

    public function allComments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class)->orderBy('sort_order');
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_saves')
            ->withTimestamps();
    }

    public function isSavedBy($userId): bool
    {
        if (!$userId) {
            return false;
        }
        return $this->savedByUsers()
            ->where('user_id', $userId)
            ->exists();
    }
}
