<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    protected $fillable = [
        'conversation_id', 'user_id', 'joined_at', 
        'last_read_message_id', 'muted', 'archived',
        'last_read_at', 'deleted_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'muted' => 'boolean',
        'archived' => 'boolean',
        'last_read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
