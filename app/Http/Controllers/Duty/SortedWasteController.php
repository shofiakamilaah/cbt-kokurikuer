<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\SortedWaste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SortedWasteController extends Controller
{
    public function index(Request $request): View
    {
        return view('duty.sorted-wastes', [
            'sortedWaste' => SortedWaste::where('classroom_id', $request->user()->classroom_id)
                ->whereDate('sorted_date', today())
                ->first(),
            'history' => SortedWaste::where('classroom_id', $request->user()->classroom_id)
                ->whereDate('sorted_date', '<', today())
                ->orderByDesc('sorted_date')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organic_weight' => ['required', 'numeric', 'between:0,999'],
            'inorganic_weight' => ['required', 'numeric', 'between:0,999'],
            'residue_weight' => ['required', 'numeric', 'between:0,999'],
        ]);

        $sortedWaste = SortedWaste::where('classroom_id', $request->user()->classroom_id)
            ->whereDate('sorted_date', today())
            ->first();

        if ($sortedWaste) {
            $sortedWaste->update([
                ...$validated,
                'user_id' => $request->user()->id,
            ]);
        } else {
            SortedWaste::create([
                ...$validated,
                'classroom_id' => $request->user()->classroom_id,
                'sorted_date' => today()->toDateString(),
                'user_id' => $request->user()->id,
            ]);
        }

        return redirect()->route('duty.sorted-wastes.index')->with('success', 'Data pemilahan sampah tersimpan.');
    }
}
