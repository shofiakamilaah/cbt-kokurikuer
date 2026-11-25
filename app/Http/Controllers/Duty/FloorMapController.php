<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use App\Models\WasteDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FloorMapController extends Controller
{
    public function index(Request $request): View
    {
        $floors = Floor::with(['rooms' => fn ($query) => $query->orderBy('name')])
            ->whereIn('name', Floor::MAP_NAMES)
            ->orderBy('level')
            ->get();
        $requestedFloorId = $request->integer('floor');
        $todayAssignment = $request->user()->todayAssignment();
        $floor = $floors->firstWhere('id', $requestedFloorId)
            ?? ($todayAssignment ? $floors->firstWhere('id', (int) $todayAssignment->floor_id) : null)
            ?? $floors->first();

        $deliveredRoomIds = $floor
            ? WasteDelivery::whereDate('delivery_date', today())
                ->whereIn('room_id', $floor->rooms->pluck('id'))
                ->pluck('room_id')
                ->all()
            : [];

        $progress = $floors->mapWithKeys(function (Floor $listedFloor): array {
            $roomIds = $listedFloor->rooms->pluck('id');

            return [
                $listedFloor->id => [
                    'delivered' => WasteDelivery::whereDate('delivery_date', today())
                        ->whereIn('room_id', $roomIds)
                        ->count(),
                    'total' => $roomIds->count(),
                ],
            ];
        });

        return view('duty.floors', [
            'floors' => $floors,
            'floor' => $floor,
            'deliveredRoomIds' => $deliveredRoomIds,
            'progress' => $progress,
        ]);
    }

    public function toggle(Request $request, Room $room): RedirectResponse
    {
        DB::transaction(function () use ($request, $room): void {
            $delivery = WasteDelivery::where('room_id', $room->id)
                ->whereDate('delivery_date', today())
                ->lockForUpdate()
                ->first();

            if ($delivery) {
                $delivery->delete();

                return;
            }

            WasteDelivery::create([
                'room_id' => $room->id,
                'user_id' => $request->user()->id,
                'delivery_date' => today()->toDateString(),
            ]);
        });

        return redirect()->route('duty.floors.index', ['floor' => $room->floor_id])
            ->with('success', 'Status ruangan berhasil diperbarui.');
    }
}
