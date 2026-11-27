<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DutyAssignment;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ToiletDutyController extends Controller
{
    public function index(Request $request): View
    {
        $floors = Floor::orderBy('level')->get();
        $floor = $this->selectFloor($request, $floors);

        $assignments = $floor ? $this->todayToiletAssignments($floor->id) : collect();

        $completedCount = $assignments->filter(fn ($a) => $a->attendance?->status === 'present')->count();
        $pendingCount = $assignments->count() - $completedCount;

        return view('duty.toilet', [
            'floor' => $floor,
            'floors' => $floors,
            'assignments' => $assignments,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    private function todayToiletAssignments(int $floorId): Collection
    {
        return DutyAssignment::with(['user.classroom', 'attendance.recorder'])
            ->whereDate('assignment_date', today())
            ->where('task_type', 'toilet_duty')
            ->where('floor_id', $floorId)
            ->orderBy('id')
            ->get();
    }

    private function selectFloor(Request $request, Collection $floors): ?Floor
    {
        $requestedFloor = $floors->firstWhere('id', $request->integer('floor_id'));
        $assignedFloor = $floors->firstWhere('id', $request->user()->todayAssignment()?->floor_id);

        return $requestedFloor ?? $assignedFloor ?? $floors->first();
    }

    public function studentLookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20'],
        ]);

        $student = User::where('nis', $validated['nis'])
            ->where('role', 'student')
            ->first();

        if (! $student) {
            return response()->json(['message' => 'NIS tidak terdaftar sebagai siswa.'], 404);
        }

        return response()->json([
            'name' => $student->name,
            'nis' => $student->nis,
            'rayon' => $student->rayon,
            'classroom' => $student->classroom?->name ?? '-',
        ]);
    }

    /**
     * Mencatat siswa yang telah melaksanakan / membersihkan toilet pada jam 12.
     * Tidak boleh menolak siswa yang sudah memiliki tugas!
     */
    public function recordCleaning(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20'],
            'floor_id' => ['nullable', 'integer', 'exists:floors,id'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $student = User::where('nis', $validated['nis'])
            ->where('role', 'student')
            ->first();

        if (! $student) {
            return back()->withInput()->withErrors(['nis' => 'NIS tidak terdaftar sebagai siswa.']);
        }

        $floors = Floor::orderBy('level')->get();
        $floor = $floors->firstWhere('id', $validated['floor_id'] ?? null)
            ?? $floors->firstWhere('id', $request->user()->todayAssignment()?->floor_id)
            ?? $floors->first();

        if (! $floor) {
            return back()->withInput()->withErrors(['floor_id' => 'Belum ada data lokasi lantai toilet.']);
        }

        // Cari atau buatkan DutyAssignment untuk siswa ini di lantai ini hari ini
        $dutyAssignment = DutyAssignment::firstOrCreate(
            [
                'user_id' => $student->id,
                'assignment_date' => today()->toDateString(),
                'task_type' => 'toilet_duty',
            ],
            [
                'floor_id' => $floor->id,
                'canteen_id' => null,
                'created_by' => $request->user()->id,
            ]
        );

        if (! $dutyAssignment->floor_id) {
            $dutyAssignment->update(['floor_id' => $floor->id]);
        }

        // Catat status sudah membersihkan (attendance status: present)
        Attendance::updateOrCreate(
            ['duty_assignment_id' => $dutyAssignment->id],
            [
                'status' => 'present',
                'note' => $validated['note'] ?? 'Sudah membersihkan toilet pada jam 12',
                'recorded_by' => $request->user()->id,
            ]
        );

        return redirect()->route('duty.toilet.index', ['floor_id' => $floor->id])
            ->with('success', "Siswa {$student->name} (NIS: {$student->nis}) berhasil dicatat telah selesai membersihkan toilet di {$floor->name}.");
    }

    /**
     * Tombol cepat untuk mengubah status pelaksanaan (Tandai Selesai / Batal).
     */
    public function toggle(Request $request, DutyAssignment $assignment): RedirectResponse
    {
        if ($assignment->task_type !== 'toilet_duty') {
            abort(403);
        }

        $attendance = $assignment->attendance;

        if ($attendance && $attendance->status === 'present') {
            $attendance->delete();
            $message = "Status piket {$assignment->user->name} diubah menjadi Belum Melaksanakan.";
        } else {
            Attendance::updateOrCreate(
                ['duty_assignment_id' => $assignment->id],
                [
                    'status' => 'present',
                    'note' => 'Sudah membersihkan toilet pada jam 12',
                    'recorded_by' => $request->user()->id,
                ]
            );
            $message = "Siswa {$assignment->user->name} berhasil ditandai SUDAH selesai membersihkan toilet.";
        }

        return back()->with('success', $message);
    }
}
