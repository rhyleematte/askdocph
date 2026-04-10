<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceBody extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'content',
        'file_path',
        'file_type',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}

