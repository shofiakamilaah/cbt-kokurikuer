<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\CanteenSale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CanteenSaleController extends Controller
{
    public function index(Request $request): View
    {
        $assignment = $request->user()->todayAssignment();
        $sales = $this->todaySales($assignment->canteen_id)->get();

        return view('duty.sales', [
            'assignment' => $assignment->load('canteen'),
            'sales' => $sales,
            'salesTotal' => $sales->sum('total'),
        ]);
    }

    private function todaySales(int $canteenId): Builder
    {
        return CanteenSale::with('user.classroom')
            ->whereDate('sale_date', today())
            ->where('canteen_id', $canteenId)
            ->latest();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'between:1,9999'],
            'price' => ['required', 'integer', 'between:0,1000000'],
        ]);

        CanteenSale::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'canteen_id' => $request->user()->todayAssignment()->canteen_id,
            'sale_date' => today()->toDateString(),
        ]);

        return redirect()->route('duty.sales.index')->with('success', 'Penjualan berhasil disimpan.');
    }

    public function destroy(Request $request, CanteenSale $sale): RedirectResponse
    {
        $assignment = $request->user()->todayAssignment();

        abort_unless(
            $sale->user?->classroom_id === $request->user()->classroom_id
                && $sale->sale_date->toDateString() === today()->toDateString()
                && $sale->canteen_id === $assignment->canteen_id,
            403
        );

        $sale->delete();

        return redirect()->route('duty.sales.index')->with('success', 'Penjualan berhasil dihapus.');
    }
}
