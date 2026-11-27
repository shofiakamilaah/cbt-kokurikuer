@extends('layouts.panel')

@section('title', 'Pencatatan Penjualan Kantin')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-primary">{{ today()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">Pencatatan Penjualan {{ $canteen->name ?? 'Kantin' }}</h1>
            <p class="mt-1 text-sm text-slate-600">Catat produk makanan yang dijual hari ini: nama makanan, harga satuan, stok awal, dan jumlah yang terjual.</p>
        </div>
        <div>
            @include('partials.archive-actions', ['type' => 'canteen_sales'])
        </div>
    </div>

    {{-- Form Pencatatan --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Input Data Makanan yang Dijual</h2>
        <p class="mt-0.5 text-xs text-slate-500">Masukkan nama makanan, harga per porsi/satuan, stok awal persediaan, dan berapa porsi/satuan yang berhasil terjual.</p>

        <form action="{{ route('duty.sales.store') }}" method="POST" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <div>
                <label for="item_name" class="mb-1 block text-sm font-semibold text-slate-700">Nama Makanan yang Dijual</label>
                <input id="item_name" name="item_name" type="text" maxlength="100"
                       value="{{ old('item_name') }}"
                       placeholder="Misal: Nasi Uduk, Roti Bakar"
                       required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                @error('item_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="price" class="mb-1 block text-sm font-semibold text-slate-700">Harga Satuan (Rp)</label>
                <input id="price" name="price" type="number" min="0" max="10000000"
                       value="{{ old('price') }}"
                       placeholder="Misal: 5000"
                       required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="initial_stock" class="mb-1 block text-sm font-semibold text-slate-700">Stok Awal</label>
                <input id="initial_stock" name="initial_stock" type="number" min="1" max="99999"
                       value="{{ old('initial_stock') }}"
                       placeholder="Misal: 30"
                       required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                @error('initial_stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="quantity" class="mb-1 block text-sm font-semibold text-slate-700">Terjual Berapa</label>
                <input id="quantity" name="quantity" type="number" min="0" max="99999"
                       value="{{ old('quantity') }}"
                       placeholder="Misal: 25"
                       required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white shadow hover:bg-forest transition">
                    + Simpan Data Penjualan Makanan
                </button>
            </div>
        </form>
    </div>

    {{-- Ringkasan Total Penjualan --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="stat-card p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Jenis Makanan</p>
            <p class="mt-2 text-2xl font-bold text-slate-800">{{ $sales->count() }} <span class="text-sm font-normal text-slate-500">menu</span></p>
        </div>
        <div class="stat-card p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Terjual</p>
            <p class="mt-2 text-2xl font-bold text-primary">{{ number_format($totalItemsSold, 0, ',', '.') }} <span class="text-sm font-normal text-slate-500">porsi / item</span></p>
        </div>
        <div class="stat-card p-5 bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-200">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Total Hasil Penjualan Keseluruhan</p>
            <p class="mt-2 text-2xl font-black text-emerald-800">Rp {{ number_format($salesTotal, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Tabel Hasil Penjualan --}}
    <section class="mt-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Tabel Hasil Penjualan Hari Ini</h2>
            <span class="rounded-full bg-olive/30 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ $sales->count() }} Produk Tercatat
            </span>
        </div>

        <div class="mt-3 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-4 py-3.5">Nama Makanan yang Dijual</th>
                        <th class="px-4 py-3.5 text-right">Harga Satuan</th>
                        <th class="px-4 py-3.5 text-center">Stok Awal</th>
                        <th class="px-4 py-3.5 text-center">Terjual</th>
                        <th class="px-4 py-3.5 text-center">Sisa Stok</th>
                        <th class="px-4 py-3.5 text-right">Hasil Penjualan</th>
                        <th class="px-4 py-3.5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $index => $sale)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $sale->item_name }}</td>
                            <td class="px-4 py-3 text-right text-slate-700">Rp {{ number_format($sale->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-slate-700">{{ $sale->initial_stock }}</td>
                            <td class="px-4 py-3 text-center font-bold text-primary">{{ $sale->quantity }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">
                                <span @class([
                                    'inline-block px-2 py-0.5 rounded text-xs font-semibold',
                                    'bg-red-100 text-red-700' => $sale->remaining_stock == 0,
                                    'bg-amber-100 text-amber-700' => $sale->remaining_stock > 0 && $sale->remaining_stock <= 3,
                                    'bg-slate-100 text-slate-700' => $sale->remaining_stock > 3,
                                ])>
                                    {{ $sale->remaining_stock }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('duty.sales.destroy', $sale) }}" method="POST"
                                      onsubmit="return confirm('Apakah kamu yakin ingin menghapus data penjualan makanan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 hover:text-red-700 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <svg class="mx-auto h-10 w-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Belum ada makanan yang dicatat hari ini.<br>
                                <span class="text-xs text-slate-400">Silakan input makanan di formulir atas.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($sales->isNotEmpty())
                    <tfoot class="border-t-2 border-slate-300 bg-slate-50 font-bold text-slate-900">
                        <tr>
                            <td colspan="4" class="px-4 py-3.5 text-right font-black uppercase text-xs tracking-wider">
                                Total Keseluruhan:
                            </td>
                            <td class="px-4 py-3.5 text-center text-primary font-black">
                                {{ number_format($totalItemsSold, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5"></td>
                            <td class="px-4 py-3.5 text-right text-base font-black text-emerald-800">
                                Rp {{ number_format($salesTotal, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>
@endsection
