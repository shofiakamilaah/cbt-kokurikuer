<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\CanteenSale;
use App\Models\DutyAssignment;
use App\Models\Floor;
use App\Models\Room;
use App\Models\WasteDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $student = $request->user();
        $assignment = $student->currentAssignment();

        if ($assignment === null || ! $student->isOnDutyToday()) {
            return redirect()->route('home')->with('success', 'Kamu tidak memiliki tugas petugas hari ini.');
        }

        $mapRoomIds = $assignment->assignment_type === 'cleaning'
            ? Room::whereHas('floor', fn ($query) => $query->whereIn('name', Floor::MAP_NAMES))->pluck('id')
            : collect();

        return view('duty.dashboard', [
            'assignment' => $assignment,
            'isCleaningOfficer' => $student->isCleaningOfficer(),
            'isToiletOfficer' => $student->isToiletOfficer(),
            'isCanteenOfficer' => $student->isCanteenOfficer(),
            'roomsDeliveredCount' => $assignment->assignment_type === 'cleaning'
                ? WasteDelivery::whereDate('delivery_date', today())
                    ->whereIn('room_id', $mapRoomIds)
                    ->count()
                : 0,
            'roomsTotalCount' => $mapRoomIds->count(),
            'toiletDutyCount' => $assignment->assignment_type === 'cleaning'
                ? DutyAssignment::whereDate('assignment_date', today())
                    ->where('task_type', 'toilet_duty')
                    ->count()
                : 0,
            'salesTotal' => $assignment->assignment_type === 'canteen'
                ? CanteenSale::whereDate('sale_date', today())->get()
                    ->sum(fn (CanteenSale $sale): int => $sale->total)
                : 0,
        ]);
    }
}
