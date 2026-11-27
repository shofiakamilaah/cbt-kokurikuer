<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private Classroom $classroom;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->classroom = Classroom::create([
            'name' => 'PPLG X-1',
            'grade_level' => 10,
        ]);
        $this->student = User::factory()->create([
            'role' => 'student',
            'classroom_id' => $this->classroom->id,
            'nis' => '12699999',
        ]);
    }

    public function test_teacher_can_view_classrooms_on_assignments_page(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('admin.assignments.index'));

        $response->assertOk();
        $response->assertSee('Penugasan Siswa');
        $response->assertSee($this->classroom->name);
    }

    public function test_teacher_can_delete_student(): void
    {
        $response = $this->actingAs($this->teacher)
            ->delete(route('admin.students.destroy', $this->student));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $this->student->id]);
    }

    public function test_teacher_cannot_delete_another_teacher(): void
    {
        $otherTeacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($this->teacher)
            ->delete(route('admin.students.destroy', $otherTeacher));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $otherTeacher->id]);
    }

    public function test_teacher_can_delete_classroom(): void
    {
        $response = $this->actingAs($this->teacher)
            ->delete(route('admin.classrooms.destroy', $this->classroom));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('classrooms', ['id' => $this->classroom->id]);

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'classroom_id' => null,
        ]);
    }

    public function test_student_cannot_delete_students_or_classrooms(): void
    {
        $responseStudent = $this->actingAs($this->student)
            ->delete(route('admin.students.destroy', $this->student));
        $responseStudent->assertForbidden();

        $responseClassroom = $this->actingAs($this->student)
            ->delete(route('admin.classrooms.destroy', $this->classroom));
        $responseClassroom->assertForbidden();
    }
}
