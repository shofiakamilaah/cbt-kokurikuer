<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->input('date', today()->toDateString());
        $classrooms = Classroom::orderBy('name')->get();
        $selectedClassroomId = $request->integer('classroom_id') ?: ($classrooms->first()?->id ?? 0);

        $selectedClassroom = $classrooms->firstWhere('id', $selectedClassroomId);

        $students = collect();
        $assignments = collect();

        if ($selectedClassroom) {
            $students = User::where('classroom_id', $selectedClassroom->id)
                ->where('role', 'student')
                ->orderBy('name')
                ->get();

            // Dapatkan atau buat duty_assignment untuk siswa pada tanggal tersebut agar memiliki foreign key absensi
            foreach ($students as $student) {
                $assignment = DutyAssignment::firstOrCreate(
                    [
                        'user_id' => $student->id,
                        'assignment_date' => $date,
                    ],
                    [
                        'task_type' => 'cleaning',
                        'floor_id' => 1,
                        'canteen_id' => null,
                        'created_by' => $request->user()->id,
                    ]
                );
            }

            $assignments = DutyAssignment::with(['user', 'attendance'])
                ->whereDate('assignment_date', $date)
                ->whereHas('user', fn ($query) => $query->where('classroom_id', $selectedClassroom->id))
                ->get()
                ->sortBy(fn ($a) => $a->user->name);
        }

        // Hitung statistik
        $presentCount = $assignments->filter(fn ($a) => $a->attendance?->status === 'present')->count();
        $permittedCount = $assignments->filter(fn ($a) => $a->attendance?->status === 'permitted')->count();
        $sickCount = $assignments->filter(fn ($a) => $a->attendance?->status === 'sick')->count();
        $absentCount = $assignments->filter(fn ($a) => $a->attendance?->status === 'absent')->count();
        $unrecordedCount = $assignments->filter(fn ($a) => $a->attendance === null)->count();

        return view('admin.attendances.index', [
            'classrooms' => $classrooms,
            'selectedClassroom' => $selectedClassroom,
            'selectedClassroomId' => $selectedClassroomId,
            'date' => $date,
            'assignments' => $assignments,
            'statusLabels' => Attendance::STATUS_LABELS,
            'stats' => [
                'total' => $assignments->count(),
                'present' => $presentCount,
                'permitted' => $permittedCount,
                'sick' => $sickCount,
                'absent' => $absentCount,
                'unrecorded' => $unrecordedCount,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:present,permitted,sick,absent'],
            'note' => ['nullable', 'array'],
            'note.*' => ['nullable', 'string', 'max:200'],
        ]);

        foreach ($validated['status'] as $assignmentId => $status) {
            Attendance::updateOrCreate(
                ['duty_assignment_id' => (int) $assignmentId],
                [
                    'status' => $status,
                    'note' => $validated['note'][$assignmentId] ?? null,
                    'recorded_by' => $request->user()->id,
                ]
            );
        }

        return redirect()->route('admin.attendances.index', [
            'classroom_id' => $validated['classroom_id'],
            'date' => $validated['date'],
        ])->with('success', 'Absensi rombel berhasil disimpan oleh Guru.');
    }
}
