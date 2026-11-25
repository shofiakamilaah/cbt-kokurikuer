@extends('layouts.panel')

@section('title', 'Penjualan Kantin')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Penjualan {{ $assignment->canteen?->name }}</h1>
    <p class="mt-1 text-sm text-slate-600">Catat penjualan hari ini. Tanggal dan kantin mengikuti penugasan kamu.</p>
    <div class="mt-4">
        @include('partials.archive-actions', ['type' => 'canteen_sales'])
    </div>

    <form action="{{ route('duty.sales.store') }}" method="POST" class="mt-6 grid gap-4 rounded-xl border border-slate-200 bg-white p-5 md:grid-cols-4">
        @csrf
        <div class="md:col-span-2">
            <label for="item_name" class="mb-1 block text-sm font-semibold">Nama barang</label>
            <input id="item_name" name="item_name" type="text" maxlength="100" value="{{ old('item_name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('item_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="quantity" class="mb-1 block text-sm font-semibold">Jumlah</label>
            <input id="quantity" name="quantity" type="number" min="1" max="9999" value="{{ old('quantity', 1) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="price" class="mb-1 block text-sm font-semibold">Harga satuan (Rp)</label>
            <input id="price" name="price" type="number" min="0" max="1000000" value="{{ old('price') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="rounded-lg bg-primary px-4 py-3 font-semibold text-white md:col-span-4">Simpan Penjualan</button>
    </form>

    <section class="mt-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-bold text-slate-900">Penjualan Hari Ini</h2>
            <p class="rounded-lg bg-olive px-4 py-2 font-bold">Total Rp{{ number_format($salesTotal, 0, ',', '.') }}</p>
        </div>
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3">Barang</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Harga satuan</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-4 py-3">{{ $sale->item_name }}</td>
                            <td class="px-4 py-3">{{ $sale->quantity }}</td>
                            <td class="px-4 py-3">Rp{{ number_format($sale->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">Rp{{ number_format($sale->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('duty.sales.destroy', $sale) }}" method="POST"
                                      onsubmit="return confirm('Hapus catatan penjualan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-red-600">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada penjualan hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
