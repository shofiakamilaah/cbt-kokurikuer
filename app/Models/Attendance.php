<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'duty_assignment_id',
        'status',
        'note',
        'recorded_by',
    ];

    public const STATUS_LABELS = [
        'present' => 'Hadir',
        'permitted' => 'Izin',
        'sick' => 'Sakit',
        'absent' => 'Alpa',
    ];

    public function dutyAssignment(): BelongsTo
    {
        return $this->belongsTo(DutyAssignment::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
