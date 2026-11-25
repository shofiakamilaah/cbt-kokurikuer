<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DutyAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    private function allowedAssignments(Request $request): Builder
    {
        $officer = $request->user();
        $officerAssignment = $officer->todayAssignment();
        $taskTypes = $officerAssignment->task_type === 'canteen'
            ? ['canteen']
            : ['cleaning', 'toilet_officer'];

        return DutyAssignment::with(['user.classroom', 'attendance'])
            ->whereDate('assignment_date', today())
            ->whereIn('task_type', $taskTypes)
            ->whereHas('user', fn ($query) => $query->where('classroom_id', $officer->classroom_id))
            ->orderBy('id');
    }

    public function index(Request $request): View
    {
        return view('duty.attendances', [
            'statusLabels' => Attendance::STATUS_LABELS,
            'assignments' => $this->allowedAssignments($request)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:present,permitted,sick,absent'],
            'note' => ['nullable', 'array'],
            'note.*' => ['nullable', 'string', 'max:200'],
        ]);

        $allowedIds = $this->allowedAssignments($request)->pluck('id')->all();
        foreach ($validated['status'] as $assignmentId => $status) {
            if (! in_array((int) $assignmentId, $allowedIds, true)) {
                continue;
            }

            Attendance::updateOrCreate(
                ['duty_assignment_id' => (int) $assignmentId],
                [
                    'status' => $status,
                    'note' => $validated['note'][$assignmentId] ?? null,
                    'recorded_by' => $request->user()->id,
                ]
            );
        }

        return redirect()->route('duty.attendances.index')->with('success', 'Absensi berhasil disimpan.');
    }
}
