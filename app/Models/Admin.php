<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'fname',
        'mname',
        'lname',
        'gender',
        'bday',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bday' => 'date',
    ];

    public function sentMessages()
    {
        return $this->hasMany(\App\Models\AdminMessage::class, 'from_admin_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(\App\Models\AdminMessage::class, 'to_admin_id');
    }

    /** Helper: full name **/
    public function getFullNameAttribute()
    {
        return trim($this->fname . ' ' . $this->mname . ' ' . $this->lname) ?: $this->email;
    }

    /** Helper: short name **/
    public function getShortNameAttribute()
    {
        return trim($this->fname . ' ' . $this->lname) ?: $this->email;
    }

    /** Used in the header **/
    public function getUsernameAttribute()
    {
        return $this->email;
    }
}
