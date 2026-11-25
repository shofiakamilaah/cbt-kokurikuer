<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Floor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $activityLogs = ActivityLog::with(['user', 'floor'])
            ->whereDate('activity_date', today())
            ->whereHas('user', fn ($query) => $query->where('classroom_id', $request->user()->classroom_id))
            ->latest()
            ->get();

        return view('duty.activities', [
            'activityLogs' => $activityLogs,
            'floors' => Floor::orderBy('name')->get(),
            'activityLabels' => ActivityLog::TYPE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity_type' => ['required', 'in:cleaning,planting,toilet_duty,sorting'],
            'floor_id' => ['nullable', 'exists:floors,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $photoPath = isset($validated['photo'])
            ? $validated['photo']->store('activities', 'public')
            : null;

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'floor_id' => $validated['floor_id'] ?? null,
            'activity_date' => today()->toDateString(),
            'activity_type' => $validated['activity_type'],
            'description' => $validated['description'] ?? null,
            'photo' => $photoPath,
        ]);

        return redirect()->route('duty.activities.index')->with('success', 'Catatan kegiatan berhasil disimpan.');
    }
}
