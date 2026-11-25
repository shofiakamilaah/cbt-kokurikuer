@extends('layouts.panel')

@section('title', 'Tugas Hari Ini')

@section('content')
    <p class="text-sm text-slate-500">{{ today()->translatedFormat('l, d F Y') }}</p>
    <h1 class="mt-1 text-2xl font-bold text-slate-900">Tugas Hari Ini</h1>

    <section class="home-hero mt-6 p-6 text-white sm:p-8">
        <p class="text-sm text-white/80">Tugas kamu</p>
        <h2 class="mt-1 text-3xl font-bold text-white">{{ $assignment->type_label }}</h2>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @if($isCleaningOfficer)
            <a href="{{ route('duty.floors.index') }}" class="dashboard-action rounded-xl border border-slate-200 bg-white p-5 hover:border-primary">
                <p class="font-bold">Denah lantai</p>
                <p class="mt-2 text-sm text-slate-600">{{ $roomsDeliveredCount }} / {{ $roomsTotalCount }} ruangan ditandai</p>
            </a>
            <a href="{{ route('duty.toilet.index') }}" class="dashboard-action rounded-xl border border-slate-200 bg-white p-5 hover:border-primary">
                <p class="font-bold">Piket toilet</p>
                <p class="mt-2 text-sm text-slate-600">{{ $toiletDutyCount }} siswa terdaftar di lantai ini.</p>
            </a>
            <a href="{{ route('duty.sorted-wastes.index') }}" class="dashboard-action rounded-xl border border-slate-200 bg-white p-5 hover:border-primary">
                <p class="font-bold">Pemilahan sampah</p>
                <p class="mt-2 text-sm text-slate-600">Catat berat sampah rombel kamu.</p>
            </a>
            @if(today()->isWednesday())
                <a href="{{ route('duty.collections.index') }}" class="dashboard-action rounded-xl border border-slate-200 bg-white p-5 hover:border-primary">
                    <p class="font-bold">Pengumpulan limbah</p>
                    <p class="mt-2 text-sm text-slate-600">Catat minyak jelantah dan cangkang telur.</p>
                </a>
            @endif
            <a href="{{ route('duty.activities.index') }}" class="dashboard-action rounded-xl border border-slate-200 bg-white p-5 hover:border-primary">
                <p class="font-bold">Catatan kegiatan</p>
                <p class="mt-2 text-sm text-slate-600">Catat kegiatan kokurikuler hari ini.</p>
            </a>
        @elseif($isToiletOfficer)
            <a href="{{ route('duty.toilet.index') }}" class="dashboard-action rounded-xl border border-slate-200 bg-white p-5 hover:border-primary">
                <p class="font-bold">Piket toilet</p>
                <p class="mt-2 text-sm text-slate-600">{{ $toiletDutyCount }} siswa terdaftar di lantai ini.</p>
            </a>
        @elseif($isCanteenOfficer)
            <a href="{{ route('duty.sales.index') }}" class="dashboard-action rounded-xl border border-slate-200 bg-white p-5 hover:border-primary">
                <p class="font-bold">Penjualan {{ $assignment->canteen?->name }}</p>
                <p class="mt-2 text-sm text-slate-600">Total hari ini: Rp{{ number_format($salesTotal, 0, ',', '.') }}</p>
            </a>
        @endif
    </section>
@endsection
