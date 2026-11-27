@extends('layouts.panel')

@section('title', 'Absensi Siswa per Rombel')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-primary">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">Absensi Siswa Kokurikuler</h1>
        </div>
        <div>
            @include('partials.archive-actions', ['type' => 'attendances'])
        </div>
    </div>

    {{-- Filter Rombel & Tanggal --}}
    <form action="{{ route('admin.attendances.index') }}" method="GET" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="min-w-[200px] flex-1">
            <label for="classroom_id" class="mb-1 block text-sm font-semibold text-slate-700">Pilih Rombel / Kelas</label>
            <select id="classroom_id" name="classroom_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" @selected($selectedClassroomId == $c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[180px]">
            <label for="date" class="mb-1 block text-sm font-semibold text-slate-700">Tanggal</label>
            <input id="date" name="date" type="date" value="{{ $date }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
        </div>

        <button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-forest transition">
            Tampilkan Rombel
        </button>
    </form>

    {{-- Kartu Ringkasan Kehadiran --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <div class="stat-card p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Siswa</p>
            <p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="stat-card p-4 border-emerald-200 bg-emerald-50/50">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Hadir</p>
            <p class="mt-1 text-xl font-bold text-emerald-800">{{ $stats['present'] }}</p>
        </div>
        <div class="stat-card p-4 border-blue-200 bg-blue-50/50">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Izin</p>
            <p class="mt-1 text-xl font-bold text-blue-800">{{ $stats['permitted'] }}</p>
        </div>
        <div class="stat-card p-4 border-amber-200 bg-amber-50/50">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Sakit</p>
            <p class="mt-1 text-xl font-bold text-amber-800">{{ $stats['sick'] }}</p>
        </div>
        <div class="stat-card p-4 border-rose-200 bg-rose-50/50">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">Alpa</p>
            <p class="mt-1 text-xl font-bold text-rose-800">{{ $stats['absent'] }}</p>
        </div>
        <div class="stat-card p-4 border-slate-200 bg-slate-50/80">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Belum Dicatat</p>
            <p class="mt-1 text-xl font-bold text-slate-600">{{ $stats['unrecorded'] }}</p>
        </div>
    </div>

    {{-- Form Absensi Siswa --}}
    <form action="{{ route('admin.attendances.store') }}" method="POST" class="mt-6">
        @csrf
        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">Daftar Kehadiran Siswa: {{ $selectedClassroom?->name ?? '-' }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="setAllPresent()" class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 transition">
                        ✓ Set Semua Hadir
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-100/70 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Rayon</th>
                            <th class="px-4 py-3">Status Kehadiran</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assignments as $index => $item)
                            @php($currentStatus = old('status.'.$item->id, $item->attendance?->status ?? 'present'))
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3.5 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-slate-900">{{ $item->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->task_label }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-slate-700">{{ $item->user->nis ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-slate-700">{{ $item->user->rayon ?? 'Belum diatur' }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @foreach($statusLabels as $statusKey => $label)
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg border px-2.5 py-1 text-xs font-medium transition
                                                {{ $currentStatus === $statusKey ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                                <input type="radio" name="status[{{ $item->id }}]" value="{{ $statusKey }}"
                                                       class="attendance-radio h-3.5 w-3.5 text-primary border-slate-300 focus:ring-primary"
                                                       data-status="{{ $statusKey }}"
                                                       @checked($currentStatus === $statusKey)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <input type="text" name="note[{{ $item->id }}]"
                                           value="{{ old('note.'.$item->id, $item->attendance?->note ?? '') }}"
                                           placeholder="Keterangan (opsional)"
                                           maxlength="200"
                                           class="w-full min-w-[160px] rounded-lg border border-slate-300 px-3 py-1.5 text-xs focus:border-primary focus:ring-1 focus:ring-primary">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                    Tidak ada siswa terdaftar di rombel ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assignments->isNotEmpty())
                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 flex justify-end">
                    <button type="submit" class="rounded-xl bg-primary px-6 py-3 font-semibold text-white shadow hover:bg-forest transition">
                        Simpan Absensi Rombel
                    </button>
                </div>
            @endif
        </div>
    </form>

    <script>
        function setAllPresent() {
            document.querySelectorAll('input.attendance-radio[data-status="present"]').forEach(radio => {
                radio.checked = true;
                // update style label
                const parentGroup = radio.closest('td');
                if (parentGroup) {
                    parentGroup.querySelectorAll('label').forEach(lbl => {
                        lbl.classList.remove('border-primary', 'bg-primary/10', 'text-primary', 'font-bold');
                        lbl.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
                    });
                    const activeLabel = radio.closest('label');
                    if (activeLabel) {
                        activeLabel.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
                        activeLabel.classList.add('border-primary', 'bg-primary/10', 'text-primary', 'font-bold');
                    }
                }
            });
        }

        // Live style update on change
        document.querySelectorAll('input.attendance-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                const parentGroup = this.closest('td');
                if (parentGroup) {
                    parentGroup.querySelectorAll('label').forEach(lbl => {
                        lbl.classList.remove('border-primary', 'bg-primary/10', 'text-primary', 'font-bold');
                        lbl.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
                    });
                    const activeLabel = this.closest('label');
                    if (activeLabel) {
                        activeLabel.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
                        activeLabel.classList.add('border-primary', 'bg-primary/10', 'text-primary', 'font-bold');
                    }
                }
            });
        });
    </script>
@endsection
