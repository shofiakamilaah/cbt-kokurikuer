@extends('layouts.panel')

@section('title', 'Pengumpulan Limbah (Rabu)')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-primary">{{ today()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">Pengumpulan Limbah (Rabu)</h1>
            <p class="mt-1 text-sm text-slate-600">Catat penyetoran minyak jelantah dan cangkang telur dari siswa. Cari siswa berdasarkan NIS.</p>
        </div>
        <div>
            @include('partials.archive-actions', ['type' => 'wednesday_collections'])
        </div>
    </div>

    {{-- Form Pencarian Siswa --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Cari Siswa Penyetor</h2>
        <p class="mt-0.5 text-xs text-slate-500">Ketik minimal 3 digit nomor NIS untuk mencari data siswa.</p>

        <form action="{{ route('duty.collections.index') }}" method="GET" class="mt-4 flex flex-wrap items-end gap-3">
            <div class="min-w-[240px] flex-1">
                <label for="keyword" class="mb-1 block text-sm font-semibold text-slate-700">Nomor Induk Siswa (NIS)</label>
                <div class="relative">
                    <input id="keyword" name="keyword" type="search" value="{{ $keyword }}" minlength="3" required
                           placeholder="Misal: 125..."
                           class="w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                </div>
                @error('keyword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-forest transition">
                Cari Siswa
            </button>
            @if(request()->filled('keyword'))
                <a href="{{ route('duty.collections.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Reset
                </a>
            @endif
        </form>

        {{-- Hasil Pencarian Siswa --}}
        @if($studentMatches->isNotEmpty())
            <div class="mt-6 border-t border-slate-100 pt-5">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Pilih Siswa & Catat Setoran</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($studentMatches as $student)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 hover:border-primary/50 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ $student->name }}</h4>
                                    <p class="text-xs text-slate-500">NIS: <span class="font-semibold text-slate-700">{{ $student->nis }}</span> &bull; {{ $student->classroom?->name ?? '-' }}</p>
                                </div>
                                <span class="rounded-md bg-white border border-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                                    {{ $student->rayon ?? 'Rayon -' }}
                                </span>
                            </div>

                            <form action="{{ route('duty.collections.store') }}" method="POST" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="contributor_id" value="{{ $student->id }}">

                                <div>
                                    <label for="cooking_oil_amount-{{ $student->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Minyak Jelantah (liter)</label>
                                    <input id="cooking_oil_amount-{{ $student->id }}" name="cooking_oil_amount" type="number" step="0.01" min="0.01" max="999"
                                           value="{{ old('cooking_oil_amount') }}"
                                           placeholder="Jumlah liter (opsional)"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs focus:border-primary focus:ring-1 focus:ring-primary">
                                    @error('cooking_oil_amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="flex items-center gap-2">
                                    <input id="eggshell_submitted-{{ $student->id }}" name="eggshell_submitted" type="checkbox" value="1" @checked(old('eggshell_submitted'))
                                           class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                                    <label for="eggshell_submitted-{{ $student->id }}" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                        Setor Cangkang Telur
                                    </label>
                                    @error('eggshell_submitted') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <button type="submit" class="w-full rounded-lg bg-primary py-2 text-xs font-bold text-white shadow hover:bg-forest transition">
                                    Simpan Setoran Siswa Ini
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif(request()->filled('keyword') && ! $errors->has('keyword'))
            <p class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs font-medium text-amber-800">
                Tidak ada siswa yang cocok dengan kata kunci NIS "{{ $keyword }}". Pastikan nomor NIS sudah benar.
            </p>
        @endif
    </div>

    {{-- Tabel Entri Setoran Hari Ini --}}
    <section class="mt-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold text-slate-900">Entri Pengumpulan Hari Ini</h2>
            <span class="rounded-full bg-olive/30 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ $collections->count() }} Penyetor
            </span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Nama Penyetor</th>
                        <th class="px-4 py-3.5">Rombel</th>
                        <th class="px-4 py-3.5">Minyak Jelantah</th>
                        <th class="px-4 py-3.5">Cangkang Telur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($collections as $collection)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $collection['contributor']->name }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $collection['contributor']->classroom?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                @if($collection['cooking_oil_amount'] > 0)
                                    <span class="inline-flex items-center gap-1 font-bold text-primary">
                                        {{ number_format($collection['cooking_oil_amount'], 2, ',', '.') }} liter
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($collection['has_eggshell'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                        ✓ Disetor
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Tidak setor</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-slate-400">
                                Belum ada siswa yang menyetor limbah hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
