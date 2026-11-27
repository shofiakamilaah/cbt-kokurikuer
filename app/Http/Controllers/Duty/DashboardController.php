<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\CanteenSale;
use App\Models\DutyAssignment;
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

        // Hanya siswa bertugas (officer) hari ini yang bisa mengakses dashboard tugas/pencatatan
        if (! $student || ! $student->isOfficer()) {
            return redirect()->route('home')->with('error', 'Kamu tidak memiliki penugasan piket/petugas kokurikuler hari ini.');
        }

        $assignment = $student->todayAssignment();

        if ($assignment === null) {
            return redirect()->route('home')->with('error', 'Kamu tidak memiliki penugasan piket/petugas kokurikuler hari ini.');
        }

        $assignment->load(['floor', 'canteen']);

        $roomsDeliveredCount = 0;
        $roomsTotalCount = 0;
        $toiletDutyCount = 0;
        $salesTotal = 0;

        if ($student->isCleaningOfficer()) {
            $floorId = $assignment->floor_id ?? 1;
            $roomIds = Room::where('floor_id', $floorId)->pluck('id');
            $roomsTotalCount = $roomIds->count();
            $roomsDeliveredCount = WasteDelivery::whereDate('delivery_date', today())
                ->whereIn('room_id', $roomIds)
                ->count();
            $toiletDutyCount = DutyAssignment::whereDate('assignment_date', today())
                ->where('task_type', 'toilet_duty')
                ->where('floor_id', $floorId)
                ->count();
        }

        if ($student->isCanteenOfficer()) {
            $canteenId = $assignment->canteen_id ?? $student->assignedCanteen()?->id ?? 1;
            $salesTotal = CanteenSale::whereDate('sale_date', today())
                ->where('canteen_id', $canteenId)
                ->get()
                ->sum(fn (CanteenSale $sale): int => $sale->total);
        }

        return view('duty.dashboard', [
            'assignment' => $assignment,
            'canteen' => $student->assignedCanteen(),
            'isCleaningOfficer' => $student->isCleaningOfficer(),
            'isToiletOfficer' => $student->isToiletOfficer(),
            'isCanteenOfficer' => $student->isCanteenOfficer(),
            'roomsDeliveredCount' => $roomsDeliveredCount,
            'roomsTotalCount' => $roomsTotalCount,
            'toiletDutyCount' => $toiletDutyCount,
            'salesTotal' => $salesTotal,
        ]);
    }
}
