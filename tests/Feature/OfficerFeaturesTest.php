<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Canteen;
use App\Models\CanteenSale;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Semester;
use App\Models\SortedWaste;
use App\Models\User;
use App\Models\WasteDelivery;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FloorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleaning_officer_can_toggle_room_delivery_for_today(): void
    {
        [$teacher, $officer, $floor] = $this->createOfficerWithAssignment('cleaning');
        $this->assertSame('cleaning', $officer->todayAssignment()?->task_type);
        $room = Room::create([
            'floor_id' => $floor->id,
            'name' => 'Ruang 101',
            'pos_x' => 5,
            'pos_y' => 8,
            'width' => 20,
            'height' => 34,
        ]);

        $this->actingAs($officer)
            ->post(route('duty.floors.toggle', $room))
            ->assertRedirect(route('duty.floors.index', ['floor' => $floor->id]));

        $this->assertTrue(WasteDelivery::where('room_id', $room->id)
            ->where('user_id', $officer->id)
            ->whereDate('delivery_date', today())
            ->exists());

        $this->actingAs($officer)
            ->post(route('duty.floors.toggle', $room));

        $this->assertFalse(WasteDelivery::where('room_id', $room->id)
            ->whereDate('delivery_date', today())
            ->exists());
    }

    public function test_canteen_officer_cannot_open_cleaning_floor_map(): void
    {
        [$teacher, $officer] = $this->createOfficerWithAssignment('canteen');

        $this->actingAs($officer)
            ->get(route('duty.floors.index'))
            ->assertForbidden();
    }

    public function test_student_without_a_cleaning_or_toilet_assignment_cannot_open_those_inputs(): void
    {
        [, $officer] = $this->createOfficerWithAssignment('cleaning');
        $student = User::create([
            'name' => 'Siswa Tanpa Penugasan',
            'email' => 'unassigned@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500005',
            'classroom_id' => $officer->classroom_id,
        ]);

        $this->actingAs($student)
            ->get(route('duty.floors.index'))
            ->assertForbidden();

        $this->get(route('duty.toilet.index'))
            ->assertForbidden();
    }

    public function test_cleaning_officer_can_open_all_cleaning_pages(): void
    {
        [, $officer] = $this->createOfficerWithAssignment('cleaning');

        $this->actingAs($officer);

        $this->get(route('duty.dashboard'))
            ->assertOk()
            ->assertSee('text-white', false)
            ->assertDontSee('Tugas Saya');
        $this->get(route('duty.floors.index'))->assertOk();
        $this->get(route('duty.attendances.index'))
            ->assertOk()
            ->assertSee('Petugas')
            ->assertSee('Simpan Absensi');
        $this->get(route('duty.sorted-wastes.index'))->assertOk();
        $this->get(route('duty.activities.index'))->assertOk();
        $this->get(route('duty.collections.index'))->assertOk();
    }

    public function test_cleaning_officer_navigation_lists_all_cleaning_records(): void
    {
        [, $officer] = $this->createOfficerWithAssignment('cleaning');
        DutySchedule::where('classroom_id', $officer->classroom_id)
            ->whereDate('duty_date', today())
            ->delete();
        DutyAssignment::where('user_id', $officer->id)
            ->whereDate('assignment_date', today())
            ->delete();

        $response = $this->actingAs($officer)->get(route('profile.index'));

        $response->assertOk()
            ->assertSee('aria-label="Navigasi panel"', false)
            ->assertSee(route('duty.floors.index'), false)
            ->assertSee(route('duty.toilet.index'), false)
            ->assertSee(route('duty.sorted-wastes.index'), false)
            ->assertSee(route('duty.activities.index'), false)
            ->assertSee(route('duty.collections.index'), false)
            ->assertDontSee(route('duty.attendances.index'), false)
            ->assertDontSee(route('duty.dashboard'), false)
            ->assertDontSee(route('duty.sales.index'), false);
    }

    public function test_cleaning_assignee_can_open_cleaning_inputs_without_todays_schedule(): void
    {
        [, $officer] = $this->createOfficerWithAssignment('cleaning');
        DutySchedule::where('classroom_id', $officer->classroom_id)
            ->whereDate('duty_date', today())
            ->delete();
        DutyAssignment::where('user_id', $officer->id)
            ->whereDate('assignment_date', today())
            ->delete();

        $this->actingAs($officer);

        $this->get(route('duty.floors.index'))->assertOk();
        $this->get(route('duty.toilet.index'))->assertOk();
        $this->get(route('duty.sorted-wastes.index'))->assertOk();
        $this->get(route('duty.activities.index'))->assertOk();
        $this->get(route('duty.collections.index'))->assertOk();

        $this->post(route('duty.sorted-wastes.store'), [
            'organic_weight' => 1.25,
            'inorganic_weight' => 2.5,
            'residue_weight' => 0,
        ])->assertRedirect(route('duty.sorted-wastes.index'));

        $this->assertTrue(SortedWaste::where('classroom_id', $officer->classroom_id)
            ->whereDate('sorted_date', today())
            ->where('user_id', $officer->id)
            ->where('organic_weight', 1.25)
            ->exists());
    }

    public function test_canteen_officer_navigation_lists_canteen_records_only(): void
    {
        [, $officer] = $this->createOfficerWithAssignment('canteen');

        $response = $this->actingAs($officer)->get(route('profile.index'));

        $response->assertOk()
            ->assertSee('aria-label="Navigasi panel"', false)
            ->assertSee(route('duty.attendances.index'), false)
            ->assertSee(route('duty.sales.index'), false)
            ->assertDontSee(route('duty.floors.index'), false)
            ->assertDontSee(route('duty.sorted-wastes.index'), false)
            ->assertDontSee(route('duty.activities.index'), false)
            ->assertDontSee(route('duty.collections.index'), false);
    }

    public function test_guest_does_not_see_authenticated_navigation(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('aria-label="Navigasi panel"', false)
            ->assertDontSee(route('duty.dashboard'), false);
    }

    public function test_floor_map_shows_four_floors_with_eight_rooms_each(): void
    {
        [, $officer] = $this->createOfficerWithAssignment('cleaning');
        $this->seed(FloorSeeder::class);

        $response = $this->actingAs($officer)->get(route('duty.floors.index'));

        $response->assertOk();
        $response->assertSee('Lantai 1 (0/8)');
        $response->assertSee('Lantai 2 (0/8)');
        $response->assertSee('Lantai 3 (0/8)');
        $response->assertSee('Lantai 4 (0/8)');
        $response->assertSee('Ruang 1');
        $response->assertSee('Ruang 8');
        $response->assertDontSee('Padjajaran');
        $this->assertDatabaseCount('rooms', 32);
    }

    public function test_cleaning_officer_updates_one_sorted_waste_record_for_today(): void
    {
        [$teacher, $officer] = $this->createOfficerWithAssignment('cleaning');

        $this->actingAs($officer)
            ->post(route('duty.sorted-wastes.store'), [
                'organic_weight' => 1.25,
                'inorganic_weight' => 2.5,
                'residue_weight' => 0,
            ])
            ->assertRedirect(route('duty.sorted-wastes.index'));

        $this->actingAs($officer)
            ->post(route('duty.sorted-wastes.store'), [
                'organic_weight' => 3.5,
                'inorganic_weight' => 1.25,
                'residue_weight' => 0.5,
            ])
            ->assertRedirect(route('duty.sorted-wastes.index'));

        $this->assertDatabaseCount('sorted_wastes', 1);
        $sortedWaste = SortedWaste::where('classroom_id', $officer->classroom_id)
            ->whereDate('sorted_date', today())
            ->first();

        $this->assertNotNull($sortedWaste);
        $this->assertSame('3.50', $sortedWaste->organic_weight);
        $this->assertSame('1.25', $sortedWaste->inorganic_weight);
        $this->assertSame('0.50', $sortedWaste->residue_weight);
    }

    public function test_sorted_waste_history_shows_previous_entries_on_the_right(): void
    {
        $this->travelTo('2026-11-24 09:00:00');
        [, $officer] = $this->createOfficerWithAssignment('cleaning');
        SortedWaste::create([
            'classroom_id' => $officer->classroom_id,
            'user_id' => $officer->id,
            'sorted_date' => '2026-11-20',
            'organic_weight' => 4,
            'inorganic_weight' => 1,
            'residue_weight' => 3,
        ]);

        $response = $this->actingAs($officer)->get(route('duty.sorted-wastes.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Pemilahan')
            ->assertSee('20/11/2026')
            ->assertSee('4,00 kg');
    }

    public function test_cleaning_officer_can_record_a_student_collection(): void
    {
        [$teacher, $officer] = $this->createOfficerWithAssignment('cleaning');
        $contributor = User::create([
            'name' => 'Penyetor',
            'email' => 'contributor@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500002',
            'classroom_id' => $officer->classroom_id,
        ]);

        $this->actingAs($officer)
            ->post(route('duty.collections.store'), [
                'contributor_id' => $contributor->id,
                'cooking_oil_amount' => 1.5,
            ])
            ->assertRedirect(route('duty.collections.index'));

        $this->assertDatabaseHas('wednesday_collections', [
            'contributor_id' => $contributor->id,
            'user_id' => $officer->id,
            'waste_type' => 'cooking_oil',
            'amount' => '1.50',
        ]);
        $this->assertDatabaseMissing('wednesday_collections', [
            'contributor_id' => $contributor->id,
            'waste_type' => 'eggshell',
        ]);
    }

    public function test_cleaning_officer_can_record_eggshell_without_a_weight_when_checked(): void
    {
        [$teacher, $officer] = $this->createOfficerWithAssignment('cleaning');
        $contributor = User::create([
            'name' => 'Penyetor',
            'email' => 'eggshell.contributor@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500004',
            'classroom_id' => $officer->classroom_id,
        ]);

        $this->actingAs($officer)
            ->post(route('duty.collections.store'), [
                'contributor_id' => $contributor->id,
                'eggshell_submitted' => '1',
            ])
            ->assertRedirect(route('duty.collections.index'));

        $this->assertDatabaseHas('wednesday_collections', [
            'contributor_id' => $contributor->id,
            'user_id' => $officer->id,
            'waste_type' => 'eggshell',
            'amount' => null,
        ]);
        $this->assertDatabaseMissing('wednesday_collections', [
            'contributor_id' => $contributor->id,
            'waste_type' => 'cooking_oil',
        ]);
    }

    public function test_cleaning_officer_cannot_save_collection_without_any_waste(): void
    {
        [, $officer] = $this->createOfficerWithAssignment('cleaning');
        $contributor = User::create([
            'name' => 'Penyetor',
            'email' => 'empty.contributor@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500005',
            'classroom_id' => $officer->classroom_id,
        ]);

        $this->actingAs($officer)
            ->post(route('duty.collections.store'), [
                'contributor_id' => $contributor->id,
            ])
            ->assertSessionHasErrors('cooking_oil_amount');

        $this->assertDatabaseCount('wednesday_collections', 0);
    }

    public function test_seeded_collection_entries_can_be_found_by_nis(): void
    {
        $this->travelTo('2026-11-24 09:00:00');
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $officer = User::where('email', 'cleaning@wikrama.test')->firstOrFail();
        $response = $this->actingAs($officer)->get(route('duty.collections.index', [
            'keyword' => '12610002',
        ]));

        $response->assertOk();
        $response->assertSee('Petugas Toilet');
        $response->assertSee('NIS 12610002');
        $response->assertSee('Minyak jelantah');
        $response->assertSee('type="checkbox"', false);
        $response->assertDontSee('Dicatat oleh');
        $response->assertDontSee('name="waste_type"', false);
        $response->assertSee('1,50 liter')
            ->assertSee('Disetor');
        $this->assertDatabaseCount('wednesday_collections', 2);
    }

    public function test_canteen_officer_can_record_and_delete_a_sale_from_their_classroom(): void
    {
        [$teacher, $officer] = $this->createOfficerWithAssignment('canteen');

        $this->actingAs($officer)
            ->get(route('profile.index'))
            ->assertOk()
            ->assertSee(route('duty.sales.index'), false)
            ->assertSee('Pencatatan Penjualan Kantin')
            ->assertDontSee(route('duty.floors.index'), false);

        $this->actingAs($officer)
            ->get(route('duty.sales.index'))
            ->assertOk();

        $this->post(route('duty.sales.store'), [
            'item_name' => 'Air mineral',
            'quantity' => 2,
            'price' => 5000,
        ])->assertRedirect(route('duty.sales.index'));

        $sale = CanteenSale::firstOrFail();
        $this->assertSame($officer->id, $sale->user_id);
        $this->get(route('duty.sales.index'))
            ->assertOk()
            ->assertSee('Air mineral')
            ->assertSee('Rp10.000');

        $this->delete(route('duty.sales.destroy', $sale))
            ->assertRedirect(route('duty.sales.index'));

        $this->assertDatabaseMissing('canteen_sales', ['id' => $sale->id]);
    }

    public function test_toilet_officer_can_add_a_registered_student_to_the_toilet_duty_list(): void
    {
        [$teacher, $officer, $floor] = $this->createOfficerWithAssignment('toilet_officer');
        $toiletDutyStudent = User::create([
            'name' => 'Siswa Piket',
            'email' => 'toilet-duty@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500002',
            'classroom_id' => $officer->classroom_id,
        ]);
        DutyAssignment::create([
            'user_id' => $toiletDutyStudent->id,
            'assignment_date' => today()->toDateString(),
            'task_type' => 'toilet_duty',
            'floor_id' => $floor->id,
            'canteen_id' => null,
            'created_by' => $teacher->id,
        ]);

        $this->actingAs($officer)
            ->get(route('duty.toilet.index'))
            ->assertOk();

        $replacement = User::create([
            'name' => 'Siswa Pengganti',
            'email' => 'replacement@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500003',
            'classroom_id' => $officer->classroom_id,
            'rayon' => 'Rayon PPLG',
        ]);

        $this->post(route('duty.toilet.replacement'), ['nis' => $replacement->nis])
            ->assertRedirect(route('duty.toilet.index', ['floor_id' => $floor->id]));

        $this->get(route('duty.toilet.student-lookup', ['nis' => $replacement->nis]))
            ->assertOk()
            ->assertJson([
                'name' => 'Siswa Pengganti',
                'nis' => '12500003',
                'rayon' => 'Rayon PPLG',
            ]);

        $this->get(route('duty.toilet.index'))
            ->assertOk()
            ->assertSee('Siswa Pengganti')
            ->assertDontSee('Kehadiran')
            ->assertDontSee('Alpa');

        $this->get(route('duty.toilet.student-lookup', ['nis' => '99999999']))
            ->assertNotFound();

        $this->assertDatabaseHas('duty_assignments', [
            'user_id' => $replacement->id,
            'task_type' => 'toilet_duty',
            'floor_id' => $floor->id,
            'created_by' => $officer->id,
        ]);
    }

    public function test_cleaning_assignee_can_record_toilet_duty_without_a_daily_assignment(): void
    {
        [$teacher, $officer, $floor] = $this->createOfficerWithAssignment('cleaning');
        DutySchedule::where('classroom_id', $officer->classroom_id)
            ->whereDate('duty_date', today())
            ->delete();
        DutyAssignment::where('user_id', $officer->id)
            ->whereDate('assignment_date', today())
            ->delete();
        $student = User::create([
            'name' => 'Siswa Piket Toilet',
            'email' => 'toilet-student@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500004',
            'classroom_id' => $officer->classroom_id,
        ]);

        $this->actingAs($officer)
            ->post(route('duty.toilet.replacement'), [
                'nis' => $student->nis,
                'floor_id' => $floor->id,
            ])
            ->assertRedirect(route('duty.toilet.index', ['floor_id' => $floor->id]));

        $this->assertTrue(DutyAssignment::where('user_id', $student->id)
            ->whereDate('assignment_date', today())
            ->where('task_type', 'toilet_duty')
            ->where('floor_id', $floor->id)
            ->where('created_by', $officer->id)
            ->exists());
    }

    private function createOfficerWithAssignment(string $taskType): array
    {
        $classroom = Classroom::create([
            'name' => 'PPLG X-1',
            'grade_level' => 10,
        ]);
        $teacher = User::create([
            'name' => 'Guru',
            'email' => 'guru@example.test',
            'password' => 'password123',
            'role' => 'teacher',
        ]);
        $officer = User::create([
            'name' => 'Petugas',
            'email' => 'officer@example.test',
            'password' => 'password123',
            'role' => 'student',
            'nis' => '12500001',
            'classroom_id' => $classroom->id,
        ]);
        $floor = Floor::create([
            'name' => 'Padjajaran 1 - Lantai 1',
            'level' => 1,
        ]);

        $assignmentData = [
            'user_id' => $officer->id,
            'assignment_date' => today()->toDateString(),
            'task_type' => $taskType,
            'floor_id' => $taskType === 'canteen' ? null : $floor->id,
            'canteen_id' => null,
            'created_by' => $teacher->id,
        ];

        if ($taskType === 'canteen') {
            $canteen = Canteen::create(['name' => 'IDS']);
            $assignmentData['canteen_id'] = $canteen->id;
        }

        DutyAssignment::create($assignmentData);

        if (in_array($taskType, ['cleaning', 'canteen'], true)) {
            $semester = Semester::create([
                'academic_year' => '2026/2027',
                'semester_number' => 1,
                'start_date' => '2026-07-13',
                'end_date' => '2026-12-19',
            ]);
            Assignment::create([
                'student_id' => $officer->id,
                'semester_id' => $semester->id,
                'assignment_type' => $taskType,
                'created_by' => $teacher->id,
            ]);
            DutySchedule::create([
                'classroom_id' => $classroom->id,
                'semester_id' => $semester->id,
                'duty_date' => today(),
            ]);
        }

        return [$teacher, $officer, $floor];
    }
}
