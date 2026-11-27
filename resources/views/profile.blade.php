@extends('layouts.panel')

@section('title', 'Profil & Arsip')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Profil & Arsip Pencatatan</h1>
        <a href="{{ route('profile.edit') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">
            Edit Profil
        </a>
    </div>

    <section class="mt-5 flex flex-wrap items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
        @if($profileUser->profile_photo_path)
            <img src="{{ asset('storage/'.$profileUser->profile_photo_path) }}" alt="Foto profil {{ $profileUser->name }}"
                 class="h-20 w-20 rounded-full object-cover">
        @else
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-primary text-2xl font-bold text-white">
                {{ mb_substr($profileUser->name, 0, 1) }}
            </span>
        @endif
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $profileUser->name }}</h2>
            <p class="text-sm text-slate-600">{{ $profileUser->email }}</p>
        </div>
    </section>

    <section class="mt-3 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">NIS</p>
            <p class="mt-1 font-semibold">{{ $profileUser->nis ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rombel</p>
            <p class="mt-1 font-semibold">{{ $profileUser->classroom?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rayon</p>
            <p class="mt-1 font-semibold">{{ $profileUser->rayon ?? 'Belum diatur' }}</p>
        </div>
    </section>

    @if($isSeniorStudent)
        {{-- Khusus Siswa Kelas 11 & 12: Tidak ada arsip harian, hanya jadwal piket toilet --}}
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Jadwal Piket Toilet Siswa</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Informasi jadwal piket toilet kamu yang diatur oleh guru dari hasil penugasan.
                    </p>
                </div>
            </div>

            {{-- Status Hari Ini --}}
            <div class="mt-5">
                @if($todayToiletDuty)
                    <div class="rounded-xl border border-teal-200 bg-teal-50/80 p-5 shadow-sm">
                        <div class="flex items-start gap-3.5">
                            <div class="rounded-xl bg-primary p-2.5 text-white shadow-sm">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">
                                    Hari Ini Bertugas
                                </span>
                                <h3 class="mt-1 text-lg font-bold text-slate-900">Kamu memiliki tugas piket toilet hari ini!</h3>
                                <p class="mt-1 text-sm text-slate-700">
                                    Lokasi: <span class="font-bold text-slate-900">{{ $todayToiletDuty->location_name }}</span>
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Tanggal: {{ $todayToiletDuty->assignment_date->translatedFormat('l, d F Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-slate-600">
                        <svg class="mx-auto h-8 w-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm font-semibold text-slate-700">Tidak ada jadwal piket toilet hari ini</p>
                        <p class="text-xs text-slate-500 mt-1">Kamu tidak memiliki jadwal piket toilet pada hari ini.</p>
                    </div>
                @endif
            </div>

            {{-- Riwayat & Jadwal Keseluruhan --}}
            <div class="mt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Daftar Penugasan Piket Toilet</h3>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">Tanggal</th>
                                <th class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">Lokasi / Lantai</th>
                                <th class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">Ditugaskan Oleh</th>
                                <th class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($toiletDuties as $duty)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-800">
                                        {{ $duty->assignment_date->translatedFormat('d/m/Y') }}
                                        @if($duty->assignment_date->isToday())
                                            <span class="ml-1.5 rounded-full bg-teal-100 px-2 py-0.5 text-xs font-semibold text-teal-800">Hari Ini</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-900">
                                        {{ $duty->location_name }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                        {{ $duty->creator?->name ?? 'Guru' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($duty->attendance)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                                {{ ucfirst($duty->attendance->status) }}
                                            </span>
                                        @elseif($duty->assignment_date->isPast() && ! $duty->assignment_date->isToday())
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                                Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                                Terjadwal
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                        Belum ada jadwal piket toilet yang tercatat untuk kamu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @else
        {{-- Arsip Harian (Hanya untuk Guru dan Siswa Kelas 10) --}}
        <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Arsip Harian</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        @if($profileUser->isTeacher())
                            Guru dapat melihat catatan siswa per rombel.
                        @else
                            Arsip hanya menampilkan pencatatan milik kamu.
                        @endif
                    </p>
                </div>
                <a href="{{ route('profile.export', request()->query()) }}"
                   class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">
                    Export Excel
                </a>
            </div>

            <form action="{{ route('profile.index') }}" method="GET" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="type" class="mb-1 block text-sm font-semibold">Jenis pencatatan</label>
                    <select id="type" name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        @foreach($dataTypes as $dataType => $label)
                            <option value="{{ $dataType }}" @selected($type === $dataType)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="semester_id" class="mb-1 block text-sm font-semibold">Semester</label>
                    <select id="semester_id" name="semester_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" @selected($selectedSemester?->id === $semester->id)>
                                {{ $semester->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($profileUser->isTeacher())
                    <div>
                        <label for="classroom_id" class="mb-1 block text-sm font-semibold">Rombel</label>
                        <select id="classroom_id" name="classroom_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                            <option value="">Semua rombel</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected((string) $selectedClassroomId === (string) $classroom->id)>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label for="date" class="mb-1 block text-sm font-semibold">Tanggal (opsional)</label>
                    <input id="date" name="date" type="date" value="{{ request('date') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div class="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-4">
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 font-semibold text-white">Tampilkan Arsip</button>
                    <a href="{{ route('profile.index', ['type' => $type, 'date' => today()->toDateString()]) }}"
                       class="rounded-lg border border-primary px-4 py-2 font-semibold text-primary">
                        Arsip hari ini
                    </a>
                </div>
            </form>

            <div class="mt-5 overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            @foreach($columns as $column)
                                <th class="whitespace-nowrap px-4 py-3">{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr class="border-b border-slate-100 last:border-0">
                                @foreach($record['values'] as $index => $value)
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($type === 'activity_logs' && $index === 5 && $record['photo_url'])
                                            <a href="{{ $record['photo_url'] }}" target="_blank" rel="noopener noreferrer" class="text-primary underline">
                                                Buka foto
                                            </a>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada arsip untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $records->links() }}
            </div>
        </section>
    @endif
@endsection
