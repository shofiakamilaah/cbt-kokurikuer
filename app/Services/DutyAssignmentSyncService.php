<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Canteen;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\Floor;
use App\Models\User;

class DutyAssignmentSyncService
{
    /**
     * Sinkronisasi duty_assignments untuk seorang siswa berdasarkan penugasan semester
     * dan seluruh jadwal piket rombelnya.
     */
    public static function syncForStudent(User $student): void
    {
        if (! $student->classroom_id) {
            return;
        }

        $defaultFloorId = Floor::first()?->id;
        $defaultCanteenId = Canteen::first()?->id;

        $assignments = Assignment::where('student_id', $student->id)->get();

        foreach ($assignments as $assignment) {
            $schedules = DutySchedule::where('classroom_id', $student->classroom_id)
                ->where('semester_id', $assignment->semester_id)
                ->get();

            foreach ($schedules as $schedule) {
                DutyAssignment::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'assignment_date' => $schedule->duty_date->toDateString(),
                    ],
                    [
                        'task_type' => $assignment->assignment_type,
                        'floor_id' => $assignment->assignment_type === 'cleaning' ? $defaultFloorId : null,
                        'canteen_id' => $assignment->assignment_type === 'canteen' ? $defaultCanteenId : null,
                        'created_by' => $assignment->created_by ?: auth()->id() ?: 1,
                    ]
                );
            }
        }
    }

    /**
     * Sinkronisasi duty_assignments untuk seluruh siswa di sebuah rombel ketika ada jadwal piket baru.
     */
    public static function syncForClassroomSchedule(Classroom $classroom, DutySchedule $schedule): void
    {
        $defaultFloorId = Floor::first()?->id;
        $defaultCanteenId = Canteen::first()?->id;

        $students = $classroom->users()->where('role', 'student')->get();

        foreach ($students as $student) {
            $assignment = Assignment::where('student_id', $student->id)
                ->where('semester_id', $schedule->semester_id)
                ->first();

            if ($assignment) {
                DutyAssignment::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'assignment_date' => $schedule->duty_date->toDateString(),
                    ],
                    [
                        'task_type' => $assignment->assignment_type,
                        'floor_id' => $assignment->assignment_type === 'cleaning' ? $defaultFloorId : null,
                        'canteen_id' => $assignment->assignment_type === 'canteen' ? $defaultCanteenId : null,
                        'created_by' => $assignment->created_by ?: auth()->id() ?: 1,
                    ]
                );
            }
        }
    }
}
