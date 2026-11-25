<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Canteen;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\Floor;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('email', 'guru@wikrama.test')->firstOrFail();
        $semester = Semester::where('academic_year', '2026/2027')
            ->where('semester_number', 1)
            ->firstOrFail();
        $today = today();
        $nextWeekday = today()->addWeekday();

        $floor = Floor::where('name', 'Lantai 1')->firstOrFail();
        $canteen = Canteen::where('name', 'Kantin Utama')->firstOrFail();
        $pplgClassroom = Classroom::where('name', 'PPLG X-1')->firstOrFail();
        $tjktClassroom = Classroom::where('name', 'TJKT X-1')->firstOrFail();

        $demoAssignments = [
            [
                'email' => 'cleaning@wikrama.test',
                'assignment_type' => 'cleaning',
                'task_type' => 'cleaning',
                'assignment_date' => $today,
                'floor_id' => $floor->id,
                'canteen_id' => null,
            ],
            [
                'email' => 'toilet@wikrama.test',
                'assignment_type' => 'cleaning',
                'task_type' => 'toilet_officer',
                'assignment_date' => $today,
                'floor_id' => $floor->id,
                'canteen_id' => null,
            ],
            [
                'email' => 'canteen@wikrama.test',
                'assignment_type' => 'canteen',
                'task_type' => 'canteen',
                'assignment_date' => $today,
                'floor_id' => null,
                'canteen_id' => $canteen->id,
            ],
            [
                'email' => 'toilet-duty@wikrama.test',
                'assignment_type' => 'cleaning',
                'task_type' => 'toilet_duty',
                'assignment_date' => $today,
                'floor_id' => $floor->id,
                'canteen_id' => null,
            ],
            [
                'email' => 'regular@wikrama.test',
                'assignment_type' => 'cleaning',
                'task_type' => 'toilet_duty',
                'assignment_date' => $nextWeekday,
                'floor_id' => $floor->id,
                'canteen_id' => null,
            ],
        ];

        foreach ($demoAssignments as $demoAssignment) {
            $student = User::where('email', $demoAssignment['email'])->firstOrFail();

            Assignment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'semester_id' => $semester->id,
                ],
                [
                    'assignment_type' => $demoAssignment['assignment_type'],
                    'created_by' => $teacher->id,
                ],
            );

            DutyAssignment::updateOrCreate(
                [
                    'user_id' => $student->id,
                    'assignment_date' => $demoAssignment['assignment_date'],
                ],
                [
                    'task_type' => $demoAssignment['task_type'],
                    'floor_id' => $demoAssignment['floor_id'],
                    'canteen_id' => $demoAssignment['canteen_id'],
                    'created_by' => $teacher->id,
                ],
            );
        }

        $schedules = [
            ['classroom' => $pplgClassroom, 'duty_date' => $today],
            ['classroom' => $tjktClassroom, 'duty_date' => $today],
            ['classroom' => $tjktClassroom, 'duty_date' => $nextWeekday],
        ];

        foreach ($schedules as $schedule) {
            DutySchedule::updateOrCreate(
                [
                    'classroom_id' => $schedule['classroom']->id,
                    'duty_date' => $schedule['duty_date'],
                ],
                ['semester_id' => $semester->id],
            );
        }
    }
}
