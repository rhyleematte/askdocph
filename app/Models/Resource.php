<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'thumbnail',
        'duration_meta',
        'hashtags',
    ];

    public function body()
    {
        return $this->hasOne(ResourceBody::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function joinedByUsers()
    {
        return $this->belongsToMany(User::class, 'resource_user')
            ->withTimestamps()
            ->withPivot('status');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getContentAttribute($value)
    {
        if (array_key_exists('content', $this->attributes)) {
            return $value;
        }

        return $this->body ? $this->body->content : null;
    }

    public function getFilePathAttribute($value)
    {
        if (array_key_exists('file_path', $this->attributes)) {
            return $value;
        }

        return $this->body ? $this->body->file_path : null;
    }

    public function getFileTypeAttribute($value)
    {
        if (array_key_exists('file_type', $this->attributes)) {
            return $value;
        }

        return $this->body ? $this->body->file_type : null;
    }

    /** Return hashtags as clean array */
    public function getHashtagsArrayAttribute(): array
    {
        if (!$this->hashtags) return [];
        return array_values(array_filter(
            array_map('trim', explode(',', $this->hashtags))
        ));
    }

    /** Get thumbnail URL */
    public function getThumbnailUrlAttribute(): string
    {
        $thumb = $this->thumbnail;

        if (!$thumb) {
            return asset('assets/img/defaultcover.png');
        }

        if (strpos($thumb, 'http') === 0) {
            return $thumb;
        }

        return asset('storage/' . ltrim($thumb, '/'));
    }

    /** Get File URL */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) return null;
        // Extract just the filename and use the resource.file route with proper headers
        $filename = basename($this->file_path);
        return route('resource.file', $filename);
    }
}
