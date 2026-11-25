<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DutyAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'assignment_date',
        'task_type',
        'floor_id',
        'canteen_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'assignment_date' => 'date',
        ];
    }

    // ── Task type constants ──

    public const TASK_LABELS = [
        'cleaning' => 'Kebersihan',
        'toilet_officer' => 'Petugas toilet',
        'toilet_duty' => 'Piket toilet',
        'canteen' => 'Kantin',
    ];

    // ── Accessors ──

    public function getTaskLabelAttribute(): string
    {
        return self::TASK_LABELS[$this->task_type] ?? $this->task_type;
    }

    public function getLocationNameAttribute(): string
    {
        if ($this->task_type === 'canteen') {
            return $this->canteen?->name ?? '-';
        }

        return $this->floor?->name ?? '-';
    }

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class);
    }
}
