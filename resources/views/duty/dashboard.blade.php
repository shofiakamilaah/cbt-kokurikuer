@extends('layouts.panel')

@section('title', 'Tugas Hari Ini')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="text-sm font-semibold text-primary">{{ today()->translatedFormat('l, d F Y') }}</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Tugas Hari Ini</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Petugas Aktif
            </span>
        </div>
    </div>

    {{-- Hero Card --}}
    <section class="home-hero mt-6 p-6 text-white sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">Tugas kamu hari ini</p>
        <h2 class="mt-1 text-3xl font-extrabold text-white">
            {{ $assignment?->task_label ?? ($isCanteenOfficer ? 'Petugas Kantin' : 'Petugas Kebersihan') }}
        </h2>
        <p class="mt-2 text-sm text-white/90 font-medium flex items-center gap-1.5">
            <svg class="h-4 w-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 116 0z"></path>
            </svg>
            {{ $assignment?->location_name ?? ($isCanteenOfficer ? ($canteen?->name ?? 'Kantin') : 'Area Tugas Sekolah') }}
        </p>
    </section>

    {{-- Action Cards --}}
    <section class="mt-6">
        <h2 class="text-base font-bold text-slate-900 mb-3">Menu Tindakan Petugas</h2>

        @if($isCanteenOfficer)
            {{-- Canteen Officer: Focus on Sales Only --}}
            <div class="grid gap-4 sm:grid-cols-1 md:max-w-xl">
                <a href="{{ route('duty.sales.index') }}" class="dashboard-action group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 group-hover:text-primary transition">Pencatatan Penjualan {{ $canteen?->name ?? 'Kantin' }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Input makanan, harga, stok awal, dan jumlah yang terjual</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-primary/10 p-2 text-primary group-hover:translate-x-1 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-xs text-slate-500 font-medium">Total Terjual Hari Ini</span>
                        <span class="text-base font-black text-emerald-700">Rp {{ number_format($salesTotal, 0, ',', '.') }}</span>
                    </div>
                </a>
            </div>

        @elseif($isCleaningOfficer)
            {{-- Cleaning Officer: Smooth Clean Layout --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {{-- 1. Denah Lantai --}}
                <a href="{{ route('duty.floors.index') }}" class="dashboard-action group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-primary hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-700 group-hover:bg-primary group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                            {{ $roomsDeliveredCount }} / {{ $roomsTotalCount }}
                        </span>
                    </div>
                    <h3 class="mt-4 font-bold text-slate-900 group-hover:text-primary transition">Denah Lantai</h3>
                    <p class="mt-1 text-xs text-slate-500">Tandai pengantaran tempat sampah per ruangan</p>
                </a>

                {{-- 2. Pemilahan Sampah --}}
                <a href="{{ route('duty.sorted-wastes.index') }}" class="dashboard-action group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-primary hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 group-hover:bg-primary group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                            Rombel
                        </span>
                    </div>
                    <h3 class="mt-4 font-bold text-slate-900 group-hover:text-primary transition">Pemilahan Sampah</h3>
                    <p class="mt-1 text-xs text-slate-500">Catat berat sampah organik, non-organik, dan residu</p>
                </a>

                {{-- 3. Catatan Kegiatan --}}
                <a href="{{ route('duty.activities.index') }}" class="dashboard-action group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-primary hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-700 group-hover:bg-primary group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            Foto & Log
                        </span>
                    </div>
                    <h3 class="mt-4 font-bold text-slate-900 group-hover:text-primary transition">Catatan Kegiatan</h3>
                    <p class="mt-1 text-xs text-slate-500">Log aktivitas kokurikuler harian rombel</p>
                </a>

                {{-- 4. Pengumpulan Limbah --}}
                <a href="{{ route('duty.collections.index') }}" class="dashboard-action group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-primary hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700 group-hover:bg-primary group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800">
                            Rabu
                        </span>
                    </div>
                    <h3 class="mt-4 font-bold text-slate-900 group-hover:text-primary transition">Pengumpulan Limbah</h3>
                    <p class="mt-1 text-xs text-slate-500">Pencatatan minyak jelantah & cangkang telur</p>
                </a>

                {{-- 5. Piket Toilet --}}
                <a href="{{ route('duty.toilet.index') }}" class="dashboard-action group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-primary hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 text-purple-700 group-hover:bg-primary group-hover:text-white transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <span class="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-800">
                            {{ $toiletDutyCount }} siswa
                        </span>
                    </div>
                    <h3 class="mt-4 font-bold text-slate-900 group-hover:text-primary transition">Piket Toilet</h3>
                    <p class="mt-1 text-xs text-slate-500">Daftar siswa piket toilet pada lantai ini</p>
                </a>
            </div>
        @endif
    </section>
@endsection
