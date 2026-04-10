<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMessage extends Model
{
    protected $fillable = ['from_admin_id', 'to_admin_id', 'body', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'from_admin_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Admin::class, 'to_admin_id');
    }
}
