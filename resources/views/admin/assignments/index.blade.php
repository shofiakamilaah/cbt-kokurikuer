@extends('layouts.panel')

@section('title', 'Penugasan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Penugasan Siswa</h1>
    </div>
    <div class="mt-4">
        @include('partials.archive-actions', ['type' => 'assignments'])
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form action="{{ route('admin.assignments.index') }}" method="GET">
            <label for="semester_id" class="mb-1 block text-sm font-semibold">Semester</label>
            <select id="semester_id" name="semester_id" class="rounded-lg border border-slate-300 px-3 py-2" onchange="this.form.submit()">
                @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}" @selected($currentSemester?->id === $semester->id)>
                        {{ $semester->label }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <section class="mt-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold text-slate-900">Rombel yang bertugas</h2>
            @if($currentSemester)
                <p class="text-sm text-slate-600">Semester {{ $currentSemester->label }}</p>
            @endif
        </div>
        <div class="grid gap-4 xl:grid-cols-2">
            @forelse($classroomCards as $classroom)
                @php
                    $cleaningCount = $classroom->users->filter(fn ($student) => $student->semesterAssignments->first()?->assignment_type === 'cleaning')->count();
                    $canteenCount = $classroom->users->filter(fn ($student) => $student->semesterAssignments->first()?->assignment_type === 'canteen')->count();
                @endphp
                <article class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">{{ $classroom->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $classroom->users->count() }} siswa · {{ $cleaningCount }} kebersihan · {{ $canteenCount }} kantin
                            </p>
                        </div>
                        @if($currentSemester)
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.assignments.import.create', [
                                    'classroom_id' => $classroom->id,
                                    'semester_id' => $currentSemester->id,
                                    'assignment_type' => 'cleaning',
                                ]) }}" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white">
                                    Unggah Kebersihan
                                </a>
                                <a href="{{ route('admin.assignments.import.create', [
                                    'classroom_id' => $classroom->id,
                                    'semester_id' => $currentSemester->id,
                                    'assignment_type' => 'canteen',
                                ]) }}" class="rounded-lg border border-primary px-3 py-2 text-sm font-semibold text-primary">
                                    Unggah Petugas Kantin
                                </a>
                            </div>
                        @endif
                    </div>

                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm font-semibold text-primary">
                            Detail siswa ({{ $classroom->users->count() }})
                        </summary>
                        <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-slate-200 bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2">Nama siswa</th>
                                        <th class="px-3 py-2">NIS</th>
                                        <th class="px-3 py-2">Penugasan</th>
                                        <th class="px-3 py-2">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($classroom->users as $student)
                                        @php($assignment = $student->semesterAssignments->first())
                                        <tr class="border-b border-slate-100 last:border-0">
                                            <td class="px-3 py-2">{{ $student->name }}</td>
                                            <td class="px-3 py-2">{{ $student->nis ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $assignment?->type_label ?? 'Belum ditentukan' }}</td>
                                            <td class="px-3 py-2">
                                                @if($assignment)
                                                    <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST"
                                                          onsubmit="return confirm('Hapus penugasan siswa ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="font-semibold text-red-600">Hapus</button>
                                                    </form>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">Belum ada data siswa di rombel ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </details>
                </article>
            @empty
                <p class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600 xl:col-span-2">
                    Belum ada rombel yang terjadwal pada semester ini. Buat jadwal rombel terlebih dahulu.
                </p>
            @endforelse
        </div>
    </section>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="font-bold text-slate-900">Buat Jadwal Rombel</h2>
        <form action="{{ route('admin.assignments.schedule') }}" method="POST" class="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_1fr_auto]">
            @csrf
            <div>
                <label for="schedule_classroom_id" class="mb-1 block text-sm font-semibold">Rombel kelas 10</label>
                <select id="schedule_classroom_id" name="classroom_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @foreach($gradeTenClassrooms as $classroom)
                        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="schedule_semester_id" class="mb-1 block text-sm font-semibold">Semester</label>
                <select id="schedule_semester_id" name="semester_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" @selected($semester->id === $currentSemester?->id)>{{ $semester->label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="schedule_first_date" class="mb-1 block text-sm font-semibold">Tanggal pertama</label>
                <input id="schedule_first_date" name="first_date" type="date" value="{{ today()->toDateString() }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <button type="submit" class="self-end rounded-lg bg-primary px-4 py-2 font-semibold text-white">Buat Jadwal</button>
        </form>
    </section>

@endsection
