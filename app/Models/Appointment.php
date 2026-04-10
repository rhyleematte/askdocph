<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'subject',
        'location',
        'description',
        'start_at',
        'end_at',
        'reminder_minutes',
        'auto_send_brief',
        'cover_image',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function invitations()
    {
        return $this->hasMany(AppointmentInvitation::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'appointment_invitations')
            ->withPivot('status')
            ->withTimestamps();
    }
}
