<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by_admin_id',
        'admin_notes',
        'professional_titles',
        'biometric_consent',
        'liveness_verified',
        'face_match_score',
        'biometric_verified_at',
        'biometric_reference_hash',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(DoctorApplicationDocument::class);
    }
}
