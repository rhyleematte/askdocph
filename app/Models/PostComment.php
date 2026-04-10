<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'parent_comment_id', 'comment_text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function replies()
    {
        return $this->hasMany(PostComment::class , 'parent_comment_id')->with('user')->latest();
    }

    public function parent()
    {
        return $this->belongsTo(PostComment::class , 'parent_comment_id');
    }
}
