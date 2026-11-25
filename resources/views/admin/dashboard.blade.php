@extends('layouts.panel')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">{{ today()->translatedFormat('l, d F Y') }}</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Dashboard Guru</h1>
        </div>
        <a href="{{ route('admin.assignments.index') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Kelola Penugasan</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="stat-card p-5">
            <p class="text-sm text-slate-500">Rombel bertugas</p>
            <p class="mt-2 text-3xl font-bold text-primary">{{ $classroomCount }} <span class="text-lg text-slate-400">/ 2</span></p>
        </article>
        <article class="stat-card p-5">
            <p class="text-sm text-slate-500">Petugas hadir</p>
            <p class="mt-2 text-3xl font-bold text-primary">{{ $officersPresentCount }} <span class="text-lg text-slate-400">/ {{ $officerAssignmentsCount }}</span></p>
        </article>
        <article class="stat-card p-5">
            <p class="text-sm text-slate-500">Ruangan tersampaikan</p>
            <p class="mt-2 text-3xl font-bold text-primary">{{ $roomsDeliveredCount }} <span class="text-lg text-slate-400">/ {{ $roomsTotalCount }}</span></p>
        </article>
        <article class="stat-card p-5">
            <p class="text-sm text-slate-500">Entri limbah hari ini</p>
            <p class="mt-2 text-3xl font-bold text-primary">{{ $collectionsTodayCount }}</p>
        </article>
    </div>

    <section class="mt-8">
        <h2 class="text-xl font-bold text-slate-900">Penugasan Hari Ini</h2>
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3">Siswa</th>
                        <th class="px-4 py-3">Rombel</th>
                        <th class="px-4 py-3">Tugas</th>
                        <th class="px-4 py-3">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-4 py-3">{{ $assignment->student->name }}</td>
                            <td class="px-4 py-3">{{ $assignment->student->classroom?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $assignment->type_label }}</td>
                            <td class="px-4 py-3">{{ $assignment->semester?->label ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada penugasan hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
