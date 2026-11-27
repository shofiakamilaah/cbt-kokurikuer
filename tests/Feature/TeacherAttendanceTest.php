<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_and_submit_classroom_attendance(): void
    {
        $teacher = User::create([
            'name' => 'Guru Kokurikuler',
            'email' => 'guru@wikrama.test',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
        ]);

        $classroom = Classroom::create(['name' => 'PPLG X-1', 'grade_level' => 10]);

        $student = User::create([
            'name' => 'Siswa Satu',
            'email' => 'siswa1@wikrama.test',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'classroom_id' => $classroom->id,
            'nis' => '12500001',
        ]);

        // Teacher views attendance page
        $response = $this->actingAs($teacher)->get(route('admin.attendances.index', [
            'classroom_id' => $classroom->id,
            'date' => today()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Absensi Siswa Kokurikuler');
        $response->assertSee('Siswa Satu');

        // Teacher submits attendance
        $assignment = DutyAssignment::where('user_id', $student->id)->first();
        $this->assertNotNull($assignment);

        $submitResponse = $this->actingAs($teacher)->post(route('admin.attendances.store'), [
            'classroom_id' => $classroom->id,
            'date' => today()->toDateString(),
            'status' => [
                $assignment->id => 'present',
            ],
            'note' => [
                $assignment->id => 'Hadir tepat waktu',
            ],
        ]);

        $submitResponse->assertRedirect();
        $submitResponse->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'duty_assignment_id' => $assignment->id,
            'status' => 'present',
            'note' => 'Hadir tepat waktu',
            'recorded_by' => $teacher->id,
        ]);
    }
}
