<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Canteen;
use App\Models\CanteenSale;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Semester;
use App\Models\SortedWaste;
use App\Models\User;
use App\Models\WasteDelivery;
use App\Models\WednesdayCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_only_their_own_records_across_archive_types(): void
    {
        $this->travelTo('2026-11-24 09:00:00');
        [$student] = $this->createArchiveData();

        $archiveContent = [
            'activity_logs' => [4, 'Catatan untuk arsip'],
            'waste_deliveries' => [1, 'Ruang 101'],
            'sorted_wastes' => [2, '1,25'],
            'wednesday_collections' => [4, 'Minyak jelantah'],
            'canteen_sales' => [2, 'Air mineral'],
            'attendances' => [4, 'Rayon PPLG'],
            'assignments' => [5, 'Kebersihan'],
            'toilet_duty' => [3, 'Rayon PPLG'],
        ];

        foreach ($archiveContent as $type => [$column, $content]) {
            $this->actingAs($student)
                ->get(route('profile.index', ['type' => $type, 'date' => today()->toDateString()]))
                ->assertOk()
                ->assertSee($content);
        }
    }

    public function test_student_archive_is_limited_to_their_own_records_and_teacher_can_filter_classrooms(): void
    {
        $this->travelTo('2026-11-24 09:00:00');
        [$student, $teacher, $otherStudent, $otherClassroom] = $this->createArchiveData();
        $classmate = User::create([
            'name' => 'Teman Satu Rombel',
            'email' => 'classmate.archive@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500003',
            'classroom_id' => $student->classroom_id,
        ]);
        ActivityLog::create([
            'user_id' => $otherStudent->id,
            'activity_date' => today()->toDateString(),
            'activity_type' => 'cleaning',
            'description' => 'Catatan rombel lain',
        ]);
        ActivityLog::create([
            'user_id' => $classmate->id,
            'activity_date' => today()->toDateString(),
            'activity_type' => 'cleaning',
            'description' => 'Catatan teman satu rombel',
        ]);

        $studentArchive = $this->actingAs($student)
            ->get(route('profile.index', ['type' => 'activity_logs']))
            ->assertOk();
        $studentArchive->assertSee('Catatan untuk arsip')
            ->assertDontSee('Catatan rombel lain')
            ->assertDontSee('Catatan teman satu rombel');

        $teacherArchive = $this->actingAs($teacher)
            ->get(route('profile.index', [
                'type' => 'activity_logs',
                'classroom_id' => $otherClassroom->id,
            ]))
            ->assertOk();
        $teacherArchive->assertSee('Catatan rombel lain')
            ->assertDontSee('Catatan untuk arsip')
            ->assertDontSee('Catatan teman satu rombel');
    }

    public function test_archive_export_is_excel_compatible_and_keeps_student_classroom_scope(): void
    {
        $this->travelTo('2026-11-24 09:00:00');
        [$student, , $otherStudent] = $this->createArchiveData();
        $classmate = User::create([
            'name' => 'Teman Satu Rombel',
            'email' => 'classmate.export@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500003',
            'classroom_id' => $student->classroom_id,
        ]);
        ActivityLog::create([
            'user_id' => $otherStudent->id,
            'activity_date' => today()->toDateString(),
            'activity_type' => 'cleaning',
            'description' => 'Data rahasia rombel lain',
        ]);
        ActivityLog::create([
            'user_id' => $classmate->id,
            'activity_date' => today()->toDateString(),
            'activity_type' => 'cleaning',
            'description' => 'Data rahasia teman satu rombel',
        ]);

        $response = $this->actingAs($student)->get(route('profile.export', [
            'type' => 'activity_logs',
            'date' => today()->toDateString(),
        ]));

        $response->assertDownload('arsip-activity_logs-20261124.csv');
        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Tanggal;Kegiatan;Rombel;Lantai;Keterangan;Foto;Pencatat', $csv);
        $this->assertStringContainsString('Catatan untuk arsip', $csv);
        $this->assertStringNotContainsString('Data rahasia rombel lain', $csv);
        $this->assertStringNotContainsString('Data rahasia teman satu rombel', $csv);
    }

    public function test_profile_archive_links_activity_photos_in_the_regular_table(): void
    {
        $this->travelTo('2026-11-24 09:00:00');
        Storage::fake('public');
        [$student] = $this->createArchiveData();
        $photoPath = UploadedFile::fake()->image('activity.png')->store('activities', 'public');
        ActivityLog::firstOrFail()->update(['photo' => $photoPath]);

        $this->actingAs($student)
            ->get(route('profile.index', [
                'type' => 'activity_logs',
                'date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Buka foto')
            ->assertSee(Storage::disk('public')->url($photoPath));
    }

    public function test_user_can_update_profile_details_and_photo(): void
    {
        Storage::fake('public');
        [$student] = $this->createArchiveData();
        $photo = UploadedFile::fake()->image('profile.png');

        $this->actingAs($student)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'nama.baru@example.test',
                'profile_photo' => $photo,
            ])
            ->assertRedirect(route('profile.index'));

        $updatedStudent = $student->fresh();
        $this->assertSame('Nama Baru', $updatedStudent->name);
        $this->assertSame('nama.baru@example.test', $updatedStudent->email);
        $this->assertNotNull($updatedStudent->profile_photo_path);
        Storage::disk('public')->assertExists($updatedStudent->profile_photo_path);
    }

    public function test_profile_update_rejects_an_email_used_by_another_account(): void
    {
        [$student, $teacher] = $this->createArchiveData();

        $this->actingAs($student)
            ->put(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => $teacher->email,
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame('Siswa Arsip', $student->fresh()->name);
    }

    /**
     * @return array{User, User, User, Classroom}
     */
    private function createArchiveData(): array
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
        $otherClassroom = Classroom::create([
            'name' => 'TJKT X-1',
            'grade_level' => 10,
        ]);
        $teacher = User::create([
            'name' => 'Guru Arsip',
            'email' => 'teacher.archive@example.test',
            'password' => 'password123',
            'role' => 'teacher',
        ]);
        $student = User::create([
            'name' => 'Siswa Arsip',
            'email' => 'student.archive@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500001',
            'classroom_id' => $classroom->id,
            'rayon' => 'Rayon PPLG',
        ]);
        $otherStudent = User::create([
            'name' => 'Siswa Rombel Lain',
            'email' => 'other.archive@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500002',
            'classroom_id' => $otherClassroom->id,
            'rayon' => 'Rayon TJKT',
        ]);
        $floor = Floor::create([
            'name' => 'Lantai 1',
            'level' => 1,
        ]);
        $room = Room::create([
            'floor_id' => $floor->id,
            'name' => 'Ruang 101',
            'pos_x' => 5,
            'pos_y' => 5,
            'width' => 20,
            'height' => 20,
        ]);
        $canteen = Canteen::create(['name' => 'Kantin Utama']);

        ActivityLog::create([
            'user_id' => $student->id,
            'floor_id' => $floor->id,
            'activity_date' => today()->toDateString(),
            'activity_type' => 'cleaning',
            'description' => 'Catatan untuk arsip',
        ]);
        WasteDelivery::create([
            'room_id' => $room->id,
            'user_id' => $student->id,
            'delivery_date' => today()->toDateString(),
        ]);
        SortedWaste::create([
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'sorted_date' => today()->toDateString(),
            'organic_weight' => 1.25,
            'inorganic_weight' => 2,
            'residue_weight' => 0.5,
        ]);
        WednesdayCollection::create([
            'contributor_id' => $student->id,
            'user_id' => $student->id,
            'collection_date' => today()->toDateString(),
            'waste_type' => 'cooking_oil',
            'amount' => 1.5,
        ]);
        CanteenSale::create([
            'user_id' => $student->id,
            'canteen_id' => $canteen->id,
            'sale_date' => today()->toDateString(),
            'item_name' => 'Air mineral',
            'quantity' => 2,
            'price' => 3000,
        ]);
        $dutyAssignment = DutyAssignment::create([
            'user_id' => $student->id,
            'assignment_date' => today()->toDateString(),
            'task_type' => 'toilet_duty',
            'floor_id' => $floor->id,
            'created_by' => $teacher->id,
        ]);
        Attendance::create([
            'duty_assignment_id' => $dutyAssignment->id,
            'status' => 'present',
            'note' => 'Hadir',
            'recorded_by' => $teacher->id,
        ]);
        Assignment::create([
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'assignment_type' => 'cleaning',
            'created_by' => $teacher->id,
        ]);

        return [$student, $teacher, $otherStudent, $otherClassroom];
    }
}
