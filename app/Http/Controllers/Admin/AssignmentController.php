<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\DutySchedule;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $semesterId = $request->integer('semester_id') ?: Semester::current()?->id;
        $currentSemester = $semesterId ? Semester::find($semesterId) : null;
        $scheduledClassroomIds = $currentSemester
            ? DutySchedule::where('semester_id', $currentSemester->id)->distinct()->pluck('classroom_id')
            : collect();
        $classroomCards = Classroom::query()
            ->where('grade_level', 10)
            ->whereIn('id', $scheduledClassroomIds)
            ->with(['users' => function (HasMany $query) use ($currentSemester): void {
                $query->where('role', 'student')
                    ->orderBy('name')
                    ->with(['semesterAssignments' => function (HasMany $assignments) use ($currentSemester): void {
                        if ($currentSemester) {
                            $assignments->where('semester_id', $currentSemester->id);
                        }
                    }]);
            }])
            ->orderBy('name')
            ->get();

        return view('admin.assignments.index', [
            'semesters' => Semester::orderByDesc('start_date')->get(),
            'currentSemester' => $currentSemester,
            'taskLabels' => Assignment::TYPES,
            'gradeTenClassrooms' => Classroom::where('grade_level', 10)->withCount('users')->orderBy('name')->get(),
            'classroomCards' => $classroomCards,
        ]);
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return back()->with('success', 'Penugasan berhasil dihapus.');
    }

    public function generateSchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'first_date' => ['required', 'date'],
        ]);

        $classroom = Classroom::findOrFail($validated['classroom_id']);
        if ((int) $classroom->grade_level !== 10) {
            return back()->withErrors(['classroom_id' => 'Jadwal hanya dapat dibuat untuk rombel kelas 10.']);
        }

        $semester = Semester::findOrFail($validated['semester_id']);
        $date = Carbon::parse($validated['first_date']);

        if ($date->lt($semester->start_date) || $date->gt($semester->end_date)) {
            return back()->withErrors(['first_date' => 'Tanggal harus berada di rentang semester.']);
        }

        $created = 0;
        while ($date->lte($semester->end_date)) {
            if (! $date->isSaturday() && ! $date->isSunday()) {
                $hasDuplicate = DutySchedule::where('classroom_id', $classroom->id)
                    ->whereDate('duty_date', $date)
                    ->exists();

                $dailyCount = DutySchedule::whereDate('duty_date', $date)->count();
                if (! $hasDuplicate && $dailyCount < 2) {
                    $dutySchedule = DutySchedule::create([
                        'classroom_id' => $classroom->id,
                        'semester_id' => $semester->id,
                        'duty_date' => $date->toDateString(),
                    ]);
                    \App\Services\DutyAssignmentSyncService::syncForClassroomSchedule($classroom, $dutySchedule);
                    $created++;
                }
            }
            $date->addDays(14);
        }

        return back()->with('success', "{$created} jadwal rombel berhasil dibuat.");
    }
}
