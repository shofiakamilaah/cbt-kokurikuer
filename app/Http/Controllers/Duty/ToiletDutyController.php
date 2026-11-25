<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\DutyAssignment;
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

        return view('duty.toilet', [
            'floor' => $floor,
            'floors' => $floors,
            'assignments' => $floor ? $this->todayToiletAssignments($floor->id) : collect(),
        ]);
    }

    private function todayToiletAssignments(int $floorId): Collection
    {
        return DutyAssignment::with('user.classroom')
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
        ]);
    }

    public function replacement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20'],
            'floor_id' => ['nullable', 'integer', 'exists:floors,id'],
        ]);

        $student = User::where('nis', $validated['nis'])
            ->where('role', 'student')
            ->first();

        if (! $student) {
            return back()->withInput()->withErrors(['nis' => 'NIS tidak terdaftar sebagai siswa.']);
        }

        if ($student->dutyAssignments()->whereDate('assignment_date', today())->exists()) {
            return back()->withInput()->withErrors(['nis' => 'Siswa tersebut sudah memiliki penugasan hari ini.']);
        }

        $floors = Floor::orderBy('level')->get();
        $floor = $floors->firstWhere('id', $validated['floor_id'] ?? null)
            ?? $floors->firstWhere('id', $request->user()->todayAssignment()?->floor_id)
            ?? $floors->first();

        if (! $floor) {
            return back()->withInput()->withErrors(['floor_id' => 'Belum ada lantai untuk mencatat piket toilet.']);
        }

        DutyAssignment::create([
            'user_id' => $student->id,
            'assignment_date' => today()->toDateString(),
            'task_type' => 'toilet_duty',
            'floor_id' => $floor->id,
            'canteen_id' => null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('duty.toilet.index', ['floor_id' => $floor->id])
            ->with('success', 'Data siswa piket toilet berhasil dicatat.');
    }
}
