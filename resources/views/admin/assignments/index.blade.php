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
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button"
                                    onclick="openStudentModal({{ $classroom->id }}, '{{ $classroom->name }}')"
                                    class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition shadow-sm flex items-center gap-1"
                                    title="Tambah data siswa manual tanpa import excel">
                                <span>+ Siswa</span>
                            </button>
                            @if($currentSemester)
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
                            @endif
                            <form action="{{ route('admin.classrooms.destroy', $classroom) }}" method="POST"
                                  onsubmit="return confirm('Hapus rombel \'{{ $classroom->name }}\'? Siswa di dalamnya akan dilepas dari rombel.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-600 hover:text-white transition" title="Hapus rombel ini">
                                    Hapus Rombel
                                </button>
                            </form>
                        </div>
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
                                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                                      onsubmit="return confirm('Hapus data siswa \'{{ $student->name }}\' secara permanen dari sistem?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus Siswa</button>
                                                </form>
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
                        <option value="{{ $classroom->id }}">{{ $classroom->name }} ({{ $classroom->users_count }} siswa)</option>
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

    {{-- Kelola Seluruh Data Rombel Kelas 10 --}}
    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <details class="group">
            <summary class="flex cursor-pointer items-center justify-between font-bold text-slate-900">
                <span class="flex items-center gap-2">
                    <span>Kelola Semua Rombel Kelas 10</span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                        {{ $gradeTenClassrooms->count() }} Rombel
                    </span>
                </span>
                <span class="text-xs text-primary group-open:rotate-180 transition-transform">&darr;</span>
            </summary>

            <div class="mt-4">
                {{-- Form Tambah Rombel Baru --}}
                <div class="mb-4 rounded-lg border border-teal-100 bg-teal-50/60 p-3.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">+ Tambah Rombel Kelas 10 Baru</h3>
                    <form action="{{ route('admin.classrooms.store') }}" method="POST" class="mt-2 flex flex-wrap gap-2">
                        @csrf
                        <input type="hidden" name="grade_level" value="10">
                        <input name="name" type="text" required placeholder="Nama rombel (contoh: PPLG X-5)"
                               class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                        <button type="submit" class="rounded-lg bg-primary px-4 py-1.5 text-sm font-semibold text-white hover:bg-forest transition">
                            Tambah
                        </button>
                    </form>
                </div>

                {{-- Grid Rombel & Tombol Hapus --}}
                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @forelse($gradeTenClassrooms as $classroom)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm">
                            <div>
                                <p class="font-bold text-slate-800">{{ $classroom->name }}</p>
                                <p class="text-xs text-slate-500">{{ $classroom->users_count }} siswa</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                        onclick="openStudentModal({{ $classroom->id }}, '{{ $classroom->name }}')"
                                        class="rounded px-2 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-50 hover:underline"
                                        title="Tambah siswa ke rombel ini">
                                    + Siswa
                                </button>
                                <form action="{{ route('admin.classrooms.destroy', $classroom) }}" method="POST"
                                      onsubmit="return confirm('Hapus rombel \'{{ $classroom->name }}\'? Siswa di dalamnya akan dilepas dari rombel ini.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 hover:underline" title="Hapus rombel">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 sm:col-span-2">Belum ada rombel kelas 10.</p>
                    @endforelse
                </div>
            </div>
        </details>
    </section>

    {{-- Form Ngambang / Floating Modal Tambah Siswa Manual --}}
    <div id="student-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all" aria-modal="true" role="dialog">
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Tambah Data Siswa</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rombel: <span id="modal-classroom-name" class="font-bold text-primary"></span></p>
                </div>
                <button type="button" onclick="closeStudentModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition" aria-label="Tutup modal">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.students.store') }}" method="POST" class="mt-4 space-y-3.5">
                @csrf
                <input type="hidden" id="modal-classroom-id" name="classroom_id" value="">
                <input type="hidden" name="semester_id" value="{{ $currentSemester?->id }}">

                <div>
                    <label for="student_name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Lengkap Siswa *</label>
                    <input id="student_name" name="name" type="text" required placeholder="Contoh: Muhammad Fajar"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="student_nis" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">NIS Siswa *</label>
                        <input id="student_nis" name="nis" type="text" maxlength="20" required placeholder="Contoh: 12610025"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label for="student_rayon" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Rayon (Opsional)</label>
                        <input id="student_rayon" name="rayon" type="text" placeholder="Contoh: Ciawi 1"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label for="student_email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email (Opsional)</label>
                    <input id="student_email" name="email" type="email" placeholder="Otomatis: [nis]@smkwikrama.sch.id jika dikosongkan"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label for="student_assignment" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Tugas Semester Ini (Opsional)</label>
                    <select id="student_assignment" name="assignment_type" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">Belum ditentukan</option>
                        <option value="cleaning">Kebersihan</option>
                        <option value="canteen">Kantin</option>
                    </select>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeStudentModal()" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="rounded-xl bg-primary px-5 py-2 text-sm font-semibold text-white shadow hover:bg-forest transition">
                        Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStudentModal(classroomId, classroomName) {
            document.getElementById('modal-classroom-id').value = classroomId;
            document.getElementById('modal-classroom-name').textContent = classroomName;
            document.getElementById('student_name').value = '';
            document.getElementById('student_nis').value = '';
            document.getElementById('student_rayon').value = '';
            document.getElementById('student_email').value = '';
            document.getElementById('student_assignment').value = '';

            const modal = document.getElementById('student-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('student_name').focus();
        }

        function closeStudentModal() {
            const modal = document.getElementById('student-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        // Tutup jika klik backdrop di luar modal
        document.getElementById('student-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStudentModal();
            }
        });

        // Tutup jika tombol ESC ditekan
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeStudentModal();
            }
        });
    </script>

@endsection
