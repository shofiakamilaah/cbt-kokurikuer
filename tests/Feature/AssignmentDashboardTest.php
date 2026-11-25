<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\DutySchedule;
use App\Models\Semester;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_assignment_when_classroom_is_on_duty(): void
    {
        $semester = Semester::create([
            'academic_year' => '2026/2027',
            'semester_number' => 1,
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
        ]);
        $classroom = Classroom::create([
            'name' => 'PPLG X-1',
            'grade_level' => 10,
        ]);
        $teacher = User::create([
            'name' => 'Guru',
            'email' => 'guru.assignment@example.test',
            'password' => 'password123',
            'role' => 'teacher',
        ]);
        $student = User::create([
            'name' => 'Siswa Petugas',
            'email' => 'student.assignment@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500001',
            'classroom_id' => $classroom->id,
        ]);

        Assignment::create([
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'assignment_type' => 'canteen',
            'created_by' => $teacher->id,
        ]);
        DutySchedule::create([
            'classroom_id' => $classroom->id,
            'semester_id' => $semester->id,
            'duty_date' => today(),
        ]);

        $response = $this->actingAs($student)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Tugas hari ini');
        $response->assertSee('Kantin');
        $response->assertSee('aria-label="Navigasi panel"', false);
        $response->assertSee(route('duty.sales.index'), false);
    }

    public function test_teacher_sees_scheduled_classroom_cards_with_separate_upload_buttons(): void
    {
        $semester = Semester::create([
            'academic_year' => '2026/2027',
            'semester_number' => 1,
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
        ]);
        $classroom = Classroom::create([
            'name' => 'PPLG X-1',
            'grade_level' => 10,
        ]);
        $teacher = User::create([
            'name' => 'Guru',
            'email' => 'guru.page@example.test',
            'password' => 'password123',
            'role' => 'teacher',
        ]);
        $student = User::create([
            'name' => 'Siswa Detail',
            'email' => 'student.detail@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500006',
            'classroom_id' => $classroom->id,
        ]);
        DutySchedule::create([
            'classroom_id' => $classroom->id,
            'semester_id' => $semester->id,
            'duty_date' => $semester->start_date,
        ]);

        $response = $this->actingAs($teacher)->get(route('admin.assignments.index', [
            'semester_id' => $semester->id,
        ]));

        $response->assertOk()
            ->assertSee('PPLG X-1')
            ->assertSee('Siswa Detail')
            ->assertSee('12500006')
            ->assertSee('Unggah Kebersihan')
            ->assertSee('Unggah Petugas Kantin')
            ->assertSee('classroom_id='.$classroom->id, false)
            ->assertSee('assignment_type=cleaning', false)
            ->assertSee('assignment_type=canteen', false)
            ->assertDontSee('Tambah atau Perbarui Penugasan');
    }

    public function test_database_seeder_shows_demo_assignments_to_teacher_and_officer(): void
    {
        $this->travelTo('2026-11-24 09:00:00');
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $teacher = User::where('email', 'guru@wikrama.test')->firstOrFail();
        $officer = User::where('email', 'cleaning@wikrama.test')->firstOrFail();

        $teacherResponse = $this->actingAs($teacher)->get(route('admin.assignments.index'));

        $teacherResponse->assertOk()
            ->assertSee('Petugas Kebersihan')
            ->assertSee('Petugas Kantin')
            ->assertSee('Unggah Kebersihan')
            ->assertSee('Unggah Petugas Kantin');
        $this->assertDatabaseCount('assignments', 5);
        $this->assertDatabaseCount('duty_assignments', 5);
        $this->assertDatabaseCount('duty_schedules', 3);
        $this->assertDatabaseCount('sorted_wastes', 3);

        $homeResponse = $this->get(route('home'));
        $homeResponse
            ->assertOk()
            ->assertSee('PPLG X-1')
            ->assertSee('TJKT X-1')
            ->assertSee(route('profile.index'), false)
            ->assertSee('Dampak Kegiatan')
            ->assertSee('Minyak jelantah')
            ->assertSee('Sampah terpilah')
            ->assertDontSee('Cangkang telur');

        $this->actingAs($teacher)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('/ 32')
            ->assertSee('Petugas Kebersihan');

        $this->actingAs($officer)->get(route('duty.dashboard'))
            ->assertOk()
            ->assertSee('0 / 32 ruangan ditandai')
            ->assertSee('text-white', false)
            ->assertDontSee('Tugas Saya');

        $officerResponse = $this->actingAs($officer)->get(route('home'));

        $officerResponse->assertOk();
        $officerResponse->assertSee('Tugas hari ini');
        $officerResponse->assertSee('Lantai 1');
        $this->get('/my-tasks')->assertNotFound();

        $this->actingAs($officer)->get(route('duty.sorted-wastes.index'))
            ->assertOk()
            ->assertSee('Riwayat Pemilahan')
            ->assertSee('23/11/2026');
    }

    public function test_guest_sees_an_empty_task_list_on_the_home_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tugas Saya')
            ->assertSee('Masuk untuk melihat tugas yang dijadwalkan.')
            ->assertDontSee('Lihat tugas saya');
    }

    public function test_classroom_and_task_specific_csv_upload_assigns_students_by_nis(): void
    {
        Storage::fake('local');

        $semester = Semester::create([
            'academic_year' => '2026/2027',
            'semester_number' => 1,
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
        ]);
        $classroom = Classroom::create([
            'name' => 'PPLG X-3',
            'grade_level' => 10,
        ]);
        $teacher = User::create([
            'name' => 'Guru',
            'email' => 'guru.csv@example.test',
            'password' => 'password123',
            'role' => 'teacher',
        ]);
        $student = User::create([
            'name' => 'Siswa CSV',
            'email' => 'student.csv@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500003',
            'classroom_id' => $classroom->id,
        ]);
        DutySchedule::create([
            'classroom_id' => $classroom->id,
            'semester_id' => $semester->id,
            'duty_date' => today(),
        ]);
        $importContext = [
            'classroom_id' => $classroom->id,
            'semester_id' => $semester->id,
            'assignment_type' => 'cleaning',
        ];

        $this->actingAs($teacher)->get(route('admin.assignments.import.create', $importContext))
            ->assertOk()
            ->assertSee('PPLG X-3')
            ->assertSee('Kebersihan');

        $this->post(route('admin.assignments.import.store'), [
            ...$importContext,
            'file' => UploadedFile::fake()->createWithContent(
                'penugasan.csv',
                "NIS\n12500003\n",
            ),
        ]);

        $preview = $this->get(route('admin.assignments.import.preview'));
        $preview->assertOk()
            ->assertSee('Siswa CSV')
            ->assertSee('12500003')
            ->assertSee('Kebersihan')
            ->assertSee('New');

        $this->post(route('admin.assignments.import.confirm'))
            ->assertRedirect(route('admin.assignments.index'));

        $this->assertDatabaseHas('assignments', [
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'assignment_type' => 'cleaning',
            'created_by' => $teacher->id,
        ]);
    }

    public function test_classroom_upload_rejects_students_from_another_classroom(): void
    {
        Storage::fake('local');
        $semester = Semester::create([
            'academic_year' => '2026/2027',
            'semester_number' => 1,
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
        ]);
        $classroom = Classroom::create([
            'name' => 'PPLG X-1',
            'grade_level' => 10,
        ]);
        $otherClassroom = Classroom::create([
            'name' => 'TJKT X-1',
            'grade_level' => 10,
        ]);
        $teacher = User::create([
            'name' => 'Guru',
            'email' => 'guru.class-scope@example.test',
            'password' => 'password123',
            'role' => 'teacher',
        ]);
        User::create([
            'name' => 'Siswa Rombel Lain',
            'email' => 'student.other-class@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500007',
            'classroom_id' => $otherClassroom->id,
        ]);
        DutySchedule::create([
            'classroom_id' => $classroom->id,
            'semester_id' => $semester->id,
            'duty_date' => today(),
        ]);

        $this->actingAs($teacher)->post(route('admin.assignments.import.store'), [
            'classroom_id' => $classroom->id,
            'semester_id' => $semester->id,
            'assignment_type' => 'canteen',
            'file' => UploadedFile::fake()->createWithContent('kantin.csv', "NIS\n12500007\n"),
        ])->assertRedirect(route('admin.assignments.import.preview'));

        $this->get(route('admin.assignments.import.preview'))
            ->assertOk()
            ->assertSee('NIS tidak terdaftar di rombel ini')
            ->assertSee('Error');

        $this->post(route('admin.assignments.import.confirm'))
            ->assertRedirect(route('admin.assignments.import.preview'));
        $this->assertDatabaseCount('assignments', 0);
    }
}
