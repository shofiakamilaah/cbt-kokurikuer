<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'floor_id',
        'activity_date',
        'activity_type',
        'description',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    public const TYPE_LABELS = [
        'cleaning' => 'Bersih-bersih',
        'planting' => 'Menanam tanaman',
        'toilet_duty' => 'Piket toilet',
        'sorting' => 'Pemilahan sampah',
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->activity_type] ?? $this->activity_type;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }
}
