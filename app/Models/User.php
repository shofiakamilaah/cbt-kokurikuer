<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis',
        'classroom_id',
        'rayon',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relationships ──

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function dutyAssignments(): HasMany
    {
        return $this->hasMany(DutyAssignment::class);
    }

    public function semesterAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'student_id');
    }

    public function currentAssignment(): ?Assignment
    {
        $semester = Semester::current();

        if (! $semester) {
            return null;
        }

        return $this->semesterAssignments()
            ->where('semester_id', $semester->id)
            ->first();
    }

    // ── Role helpers ──

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    // ── Today's assignment helpers ──

    public function isOnDutyToday(): bool
    {
        if (! $this->classroom_id || (int) $this->classroom?->grade_level !== 10) {
            return false;
        }

        return DutySchedule::whereDate('duty_date', today())
            ->where('classroom_id', $this->classroom_id)
            ->exists();
    }

    public function todayAssignment(): ?DutyAssignment
    {
        return $this->dutyAssignments()
            ->whereDate('assignment_date', today()->toDateString())
            ->first();
    }

    public function hasCleaningAssignment(): bool
    {
        return $this->isStudent()
            && $this->currentAssignment()?->assignment_type === 'cleaning';
    }

    public function isCleaningOfficer(): bool
    {
        return $this->isOnDutyToday() && $this->hasCleaningAssignment();
    }

    public function isToiletOfficer(): bool
    {
        $assignment = $this->todayAssignment();

        return $assignment !== null && $assignment->task_type === 'toilet_officer';
    }

    public function isCanteenOfficer(): bool
    {
        return $this->isOnDutyToday()
            && $this->currentAssignment()?->assignment_type === 'canteen';
    }

    public function isOfficer(): bool
    {
        return $this->isCleaningOfficer() || $this->isToiletOfficer() || $this->isCanteenOfficer();
    }

    /**
     * Assignments from today through today()->addWeekday() (the reminder window).
     * Friday/weekends look ahead to Monday.
     */
    public function reminderAssignments()
    {
        $start = today();
        $end = today()->addWeekday();
        $semester = Semester::current();

        if (! $this->classroom_id || ! $semester) {
            return collect();
        }

        $assignment = $this->currentAssignment();
        if (! $assignment) {
            return collect();
        }

        return DutySchedule::where('classroom_id', $this->classroom_id)
            ->whereBetween('duty_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('duty_date')
            ->get()
            ->map(function (DutySchedule $schedule) use ($assignment) {
                return (object) [
                    'task_label' => $assignment->type_label,
                    'location_name' => '-',
                    'assignment_date' => $schedule->duty_date,
                ];
            });
    }
}
