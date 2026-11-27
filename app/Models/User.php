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
            return $this->semesterAssignments()->latest()->first();
        }

        return $this->semesterAssignments()
            ->where('semester_id', $semester->id)
            ->first() ?? $this->semesterAssignments()->latest()->first();
    }

    // ── Role & Grade helpers ──

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isGrade10(): bool
    {
        return (int) ($this->classroom?->grade_level ?? 0) === 10;
    }

    public function isSeniorStudent(): bool
    {
        $grade = (int) ($this->classroom?->grade_level ?? 0);
        if ($grade === 11 || $grade === 12) {
            return true;
        }

        $name = $this->classroom?->name ?? '';
        return str_contains($name, 'XI') || str_contains($name, 'XII');
    }

    // ── Today's assignment helpers (STRICT FOR TODAY ONLY) ──

    public function todayAssignment(): ?DutyAssignment
    {
        return $this->dutyAssignments()
            ->whereDate('assignment_date', today()->toDateString())
            ->first();
    }

    public function isCleaningOfficer(): bool
    {
        if (! $this->isStudent() || $this->isSeniorStudent()) {
            return false;
        }

        $today = $this->todayAssignment();

        return $today !== null && in_array($today->task_type, ['cleaning', 'toilet_officer']);
    }

    public function isToiletOfficer(): bool
    {
        return $this->isCleaningOfficer();
    }

    public function isCanteenOfficer(): bool
    {
        if (! $this->isStudent() || $this->isSeniorStudent()) {
            return false;
        }

        $today = $this->todayAssignment();

        return $today !== null && $today->task_type === 'canteen';
    }

    public function isOfficer(): bool
    {
        return $this->isCleaningOfficer() || $this->isCanteenOfficer();
    }

    public function isOnDutyToday(): bool
    {
        return $this->isOfficer();
    }

    public function hasCleaningAssignment(): bool
    {
        return $this->isCleaningOfficer();
    }

    // ── Toilet Duty Helpers (For any student, including Grade 11 & 12) ──

    public function todayToiletDuty(): ?DutyAssignment
    {
        return $this->dutyAssignments()
            ->whereDate('assignment_date', today()->toDateString())
            ->where('task_type', 'toilet_duty')
            ->with('floor')
            ->first();
    }

    public function upcomingToiletDuties()
    {
        return $this->dutyAssignments()
            ->where('task_type', 'toilet_duty')
            ->whereDate('assignment_date', '>=', today()->toDateString())
            ->orderBy('assignment_date')
            ->with('floor')
            ->get();
    }

    public function assignedCanteen(): ?Canteen
    {
        if ($this->todayAssignment()?->canteen) {
            return $this->todayAssignment()->canteen;
        }

        $duty = $this->dutyAssignments()
            ->where('task_type', 'canteen')
            ->whereNotNull('canteen_id')
            ->latest('assignment_date')
            ->first();

        if ($duty && $duty->canteen) {
            return $duty->canteen;
        }

        return Canteen::first();
    }

    /**
     * Assignments from today through today()->addWeekday() (the reminder window).
     * Friday/weekends look ahead to Monday.
     */
    public function reminderAssignments()
    {
        $start = today();
        $end = today()->addWeekday();

        return $this->dutyAssignments()
            ->whereBetween('assignment_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('assignment_date')
            ->with(['floor', 'canteen'])
            ->get();
    }
}
