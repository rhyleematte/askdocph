<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAffirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote',
        'author',
        'is_published',
        'publish_at',
        'created_by_admin_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'publish_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function scopeLive($query, $at = null)
    {
        $at = $at ?: now();

        return $query
            ->where('is_published', true)
            ->where(function ($q) use ($at) {
                $q->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', $at);
            });
    }

    public static function current($at = null): ?self
    {
        $at = $at ?: now();

        return static::query()
            ->live($at)
            ->orderByRaw('CASE WHEN publish_at IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('publish_at')
            ->orderByDesc('updated_at')
            ->first();
    }

    public function getDisplayStatusAttribute(): string
    {
        if (!$this->is_published) {
            return 'Draft';
        }

        if ($this->publish_at && $this->publish_at->isFuture()) {
            return 'Scheduled';
        }

        if (isset($this->is_current_live) && $this->is_current_live) {
            return 'Live';
        }

        // If it's in the past and not marked explicitly as the current live one
        return 'Offline';
    }
}
