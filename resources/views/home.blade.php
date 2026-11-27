@extends('layouts.app')

@section('title', 'Beranda - Kokurikuler Wikrama')

@section('content')
    @auth
        @include('partials.reminder', ['reminderAssignments' => $reminderAssignments])
    @endauth

    <section class="home-hero px-6 py-8 text-white sm:px-10 sm:py-10">
        <div class="relative z-10 grid items-end gap-8 md:grid-cols-[1fr_auto]">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold tracking-wide text-white/90">
                    <span class="h-2 w-2 rounded-full bg-olive"></span>
                    {{ today()->translatedFormat('l, d F Y') }}
                </p>
                @if($semesterLabel)
                    <p class="mt-4 text-sm font-medium text-white/75">{{ $semesterLabel }}</p>
                @endif
                <h1 class="mt-2 text-3xl font-bold text-white sm:text-5xl">Bergerak bersama,<br class="hidden sm:block"> memberi dampak nyata.</h1>
                <p class="mt-4 max-w-2xl leading-7 text-white/80">Pantau petugas dan kontribusi kegiatan kokurikuler kelas 10 dalam satu ruang.</p>
                @guest
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-forest shadow-lg hover:-translate-y-0.5">Masuk ke akun</a>
                        <a href="{{ route('register') }}" class="rounded-xl border border-white/30 bg-white/10 px-5 py-3 text-sm font-bold text-white hover:bg-white/15">Daftar sebagai siswa</a>
                    </div>
                @endguest
            </div>
            <div class="w-full max-w-sm rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-sm md:w-56">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/65">Rombel bertugas hari ini</p>
                @if($dutyClassrooms->isEmpty())
                    <p class="mt-3 text-sm text-white/80">Belum ada rombel bertugas.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach($dutyClassrooms as $classroom)
                            <li class="rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-sm font-semibold text-white">
                                {{ $classroom->name }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    <section class="mt-8">
        <h2 class="text-xl font-bold text-slate-900">Tugas Saya</h2>
        @if($todayAssignment)
            <article class="mt-4 rounded-xl bg-gradient-to-br from-forest to-primary p-6 text-white">
                <p class="text-sm font-semibold text-white/75">Tugas hari ini</p>
                <h3 class="mt-2 text-2xl font-bold">{{ $todayAssignment->task_label }}</h3>
            <p class="mt-1 text-sm text-white/80">{{ $todayAssignment->location_name }}</p>
            </article>
        @endif
        @if($myTasks->isEmpty())
            <p class="mt-4 rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
                @guest
                    Masuk untuk melihat tugas yang dijadwalkan.
                @else
                    Belum ada tugas yang dijadwalkan.
                @endguest
            </p>
        @else
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach($myTasks as $task)
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-primary">{{ $task->assignment_date->translatedFormat('l, d F Y') }}</p>
                        <h3 class="mt-2 text-lg font-bold">{{ $task->task_label }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $task->location_name }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-8">
        <h2 class="text-xl font-bold text-slate-900">Dampak Kegiatan</h2>
        <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-3">
            <article class="stat-card p-5">
                <p class="text-sm text-slate-500">Minyak jelantah</p>
                <p class="mt-2 text-2xl font-bold text-primary">{{ number_format($cookingOilTotal, 2, ',', '.') }} L</p>
            </article>
            <article class="stat-card p-5">
                <p class="text-sm text-slate-500">Sampah terpilah</p>
                <p class="mt-2 text-2xl font-bold text-primary">{{ number_format($sortedWasteTotal, 2, ',', '.') }} kg</p>
            </article>
            <article class="stat-card p-5">
                <p class="text-sm text-slate-500">Catatan kegiatan</p>
                <p class="mt-2 text-2xl font-bold text-primary">{{ $activityLogTotal }}</p>
            </article>
        </div>
    </section>
@endsection
