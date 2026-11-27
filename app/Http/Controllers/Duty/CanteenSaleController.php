<?php

namespace App\Http\Controllers\Duty;

use App\Http\Controllers\Controller;
use App\Models\Canteen;
use App\Models\CanteenSale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CanteenSaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $canteen = $user->assignedCanteen() ?? Canteen::first();
        $canteenId = $canteen?->id ?? 1;

        $sales = $this->todaySales($canteenId)->get();

        return view('duty.sales', [
            'canteen' => $canteen,
            'assignment' => $user->todayAssignment(),
            'sales' => $sales,
            'salesTotal' => $sales->sum('total'),
            'totalItemsSold' => $sales->sum('quantity'),
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
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'initial_stock' => ['required', 'integer', 'min:1', 'max:99999'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99999', 'lte:initial_stock'],
        ], [
            'item_name.required' => 'Nama makanan yang dijual wajib diisi.',
            'price.required' => 'Harga satuan wajib diisi.',
            'initial_stock.required' => 'Stok awal wajib diisi.',
            'quantity.required' => 'Jumlah terjual wajib diisi.',
            'quantity.lte' => 'Jumlah terjual tidak boleh melebihi stok awal.',
        ]);

        $user = $request->user();
        $canteen = $user->assignedCanteen() ?? Canteen::first();

        CanteenSale::create([
            'user_id' => $user->id,
            'canteen_id' => $canteen?->id ?? 1,
            'sale_date' => today()->toDateString(),
            'item_name' => $validated['item_name'],
            'price' => $validated['price'],
            'initial_stock' => $validated['initial_stock'],
            'quantity' => $validated['quantity'],
        ]);

        return redirect()->route('duty.sales.index')->with('success', 'Catatan penjualan makanan berhasil disimpan.');
    }

    public function destroy(Request $request, CanteenSale $sale): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            ($sale->user_id === $user->id || $sale->user?->classroom_id === $user->classroom_id)
                && $sale->sale_date->toDateString() === today()->toDateString(),
            403
        );

        $sale->delete();

        return redirect()->route('duty.sales.index')->with('success', 'Catatan penjualan berhasil dihapus.');
    }
}
