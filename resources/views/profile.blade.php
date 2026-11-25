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
@endsection
