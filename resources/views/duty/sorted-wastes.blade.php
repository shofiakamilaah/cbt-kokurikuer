@extends('layouts.panel')

@section('title', 'Pemilahan Sampah Rombel')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-primary">{{ today()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">Pemilahan Sampah Rombel</h1>
            <p class="mt-1 text-sm text-slate-600">Catat berat sampah hasil pemilahan rombel kamu hari ini dalam satuan kilogram (kg).</p>
        </div>
        <div>
            @include('partials.archive-actions', ['type' => 'sorted_wastes'])
        </div>
    </div>

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
        {{-- Form Input Sampah --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Formulir Timbangan Sampah</h2>
            <p class="mt-0.5 text-xs text-slate-500">Masukkan berat sampah per kategori untuk rombel kamu hari ini.</p>

            <form action="{{ route('duty.sorted-wastes.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                {{-- Organik --}}
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                    <label for="organic_weight" class="flex items-center justify-between text-sm font-bold text-emerald-900">
                        <span class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            Sampah Organik (kg)
                        </span>
                        <span class="text-xs font-normal text-emerald-700">Sisa makanan, daun, dll.</span>
                    </label>
                    <div class="relative mt-2">
                        <input id="organic_weight" name="organic_weight" type="number" step="0.01" min="0" max="999"
                               value="{{ old('organic_weight', $sortedWaste?->organic_weight ?? '') }}" required
                               placeholder="0.00"
                               class="w-full rounded-xl border border-emerald-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary">
                        <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs font-bold text-slate-400">kg</span>
                    </div>
                    @error('organic_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Non-organik --}}
                <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                    <label for="inorganic_weight" class="flex items-center justify-between text-sm font-bold text-blue-900">
                        <span class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                            Sampah Non-organik (kg)
                        </span>
                        <span class="text-xs font-normal text-blue-700">Plastik, kertas, botol, dll.</span>
                    </label>
                    <div class="relative mt-2">
                        <input id="inorganic_weight" name="inorganic_weight" type="number" step="0.01" min="0" max="999"
                               value="{{ old('inorganic_weight', $sortedWaste?->inorganic_weight ?? '') }}" required
                               placeholder="0.00"
                               class="w-full rounded-xl border border-blue-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary">
                        <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs font-bold text-slate-400">kg</span>
                    </div>
                    @error('inorganic_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Residu --}}
                <div class="rounded-xl border border-amber-100 bg-amber-50/40 p-4">
                    <label for="residue_weight" class="flex items-center justify-between text-sm font-bold text-amber-900">
                        <span class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            Sampah Residu (kg)
                        </span>
                        <span class="text-xs font-normal text-amber-700">Tisu kotor, styrofoam, dll.</span>
                    </label>
                    <div class="relative mt-2">
                        <input id="residue_weight" name="residue_weight" type="number" step="0.01" min="0" max="999"
                               value="{{ old('residue_weight', $sortedWaste?->residue_weight ?? '') }}" required
                               placeholder="0.00"
                               class="w-full rounded-xl border border-amber-200 bg-white px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:border-primary focus:ring-1 focus:ring-primary">
                        <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs font-bold text-slate-400">kg</span>
                    </div>
                    @error('residue_weight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white shadow hover:bg-forest transition">
                    Simpan Berat Sampah Hari Ini
                </button>
            </form>

            @if($sortedWaste)
                <p class="mt-4 text-center text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg py-2">
                    ✓ Data timbangan hari ini sudah tercatat. Kamu dapat mengedit dan menyimpannya kembali.
                </p>
            @endif
        </div>

        {{-- Riwayat Pemilahan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Riwayat Pemilahan Rombel</h2>
                <span class="text-xs font-semibold text-slate-500">{{ $history->count() }} Hari Tercatat</span>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold text-emerald-800">Organik</th>
                            <th class="px-4 py-3 font-semibold text-blue-800">Non-organik</th>
                            <th class="px-4 py-3 font-semibold text-amber-800">Residu</th>
                            <th class="px-4 py-3 font-semibold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($history as $entry)
                            @php($total = (float)$entry->organic_weight + (float)$entry->inorganic_weight + (float)$entry->residue_weight)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $entry->sorted_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-emerald-700 font-semibold">{{ number_format((float) $entry->organic_weight, 2, ',', '.') }} kg</td>
                                <td class="px-4 py-3 text-blue-700 font-semibold">{{ number_format((float) $entry->inorganic_weight, 2, ',', '.') }} kg</td>
                                <td class="px-4 py-3 text-amber-700 font-semibold">{{ number_format((float) $entry->residue_weight, 2, ',', '.') }} kg</td>
                                <td class="px-4 py-3 text-right font-black text-slate-900">{{ number_format($total, 2, ',', '.') }} kg</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat pemilahan sebelumnya.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
