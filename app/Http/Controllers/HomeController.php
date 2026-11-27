<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\Semester;
use App\Models\SortedWaste;
use App\Models\WednesdayCollection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $today = today()->toDateString();

        $semester = Semester::current();

        // Duty classrooms: distinct classrooms from today's duty-task assignments
        $dutyClassrooms = DutyAssignment::with('user.classroom')
            ->whereDate('assignment_date', $today)
            ->whereIn('task_type', ['cleaning', 'toilet_officer', 'canteen'])
            ->get()
            ->pluck('user.classroom')
            ->filter()
            ->unique('id')
            ->values();

        $sortedWasteTotal = (float) SortedWaste::query()
            ->get(['organic_weight', 'inorganic_weight', 'residue_weight'])
            ->sum(fn (SortedWaste $sortedWaste): float => (float) $sortedWaste->organic_weight
                + (float) $sortedWaste->inorganic_weight
                + (float) $sortedWaste->residue_weight
            );

        $student = $request->user();
        $isStudent = $student?->isStudent() ?? false;
        $todayAssignment = $isStudent
            ? $student->todayAssignment()
            : null;
        $reminderAssignments = $isStudent
            ? $student->reminderAssignments()
            : collect();
        $myTasks = $isStudent
            ? $student->dutyAssignments()
                ->where('assignment_date', '>=', today()->toDateString())
                ->orderBy('assignment_date')
                ->with(['floor', 'canteen'])
                ->get()
            : collect();

        return view('home', [
            'dutyClassrooms' => $dutyClassrooms,
            'cookingOilTotal' => (float) WednesdayCollection::where('waste_type', 'cooking_oil')->sum('amount'),
            'sortedWasteTotal' => $sortedWasteTotal,
            'activityLogTotal' => ActivityLog::count(),
            'semesterLabel' => $semester?->label,
            'reminderAssignments' => $reminderAssignments,
            'myTasks' => $myTasks,
            'todayAssignment' => $todayAssignment,
        ]);
    }
}
