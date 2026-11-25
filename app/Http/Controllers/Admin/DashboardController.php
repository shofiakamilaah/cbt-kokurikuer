<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\DutySchedule;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Semester;
use App\Models\WasteDelivery;
use App\Models\WednesdayCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today()->toDateString();
        $semester = Semester::current();
        $schedules = DutySchedule::with('classroom')->whereDate('duty_date', $today)->get();
        $classroomCount = $schedules->pluck('classroom_id')->unique()->count();
        $mapRooms = Room::whereHas(
            'floor',
            fn ($query) => $query->whereIn('name', Floor::MAP_NAMES),
        );
        $assignments = $this->todayAssignments($semester, $schedules->pluck('classroom_id'))->get();

        return view('admin.dashboard', [
            'classroomCount' => $classroomCount,
            'officersPresentCount' => 0,
            'officerAssignmentsCount' => $assignments->count(),
            'roomsDeliveredCount' => WasteDelivery::whereDate('delivery_date', $today)
                ->whereIn('room_id', (clone $mapRooms)->select('rooms.id'))
                ->count(),
            'roomsTotalCount' => $mapRooms->count(),
            'collectionsTodayCount' => WednesdayCollection::where('collection_date', $today)->count(),
            'assignments' => $assignments,
        ]);
    }

    private function todayAssignments(?Semester $semester, Collection $classroomIds): Builder
    {
        $query = Assignment::with(['student.classroom', 'semester']);

        if ($semester === null || $classroomIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('semester_id', $semester->id)
            ->whereHas('student', fn ($studentQuery) => $studentQuery->whereIn('classroom_id', $classroomIds));
    }
}
