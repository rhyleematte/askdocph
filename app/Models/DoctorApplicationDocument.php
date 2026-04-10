<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_application_id',
        'doctor_requirement_id',
        'document_type',
        'file_path',
        'text_value',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(DoctorApplication::class);
    }

    public function requirement()
    {
        return $this->belongsTo(DoctorRequirement::class);
    }
}
