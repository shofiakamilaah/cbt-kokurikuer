@extends('layouts.panel')

@section('title', 'Pencatatan Piket Toilet')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-800">
                    <span class="h-2 w-2 rounded-full bg-purple-600 animate-pulse"></span>
                    Piket Jam 12:00 WIB
                </span>
                <p class="text-sm font-semibold text-slate-500">{{ today()->translatedFormat('l, d F Y') }}</p>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Pencatatan Kebersihan Toilet {{ $floor?->name ?? '' }}</h1>
            <p class="mt-1 text-sm text-slate-600">Catat dan verifikasi siswa yang sudah melaksanakan tugas bersih-bersih toilet jam 12 pada lantai ini.</p>
        </div>
        <div>
            @include('partials.archive-actions', ['type' => 'toilet_duty'])
        </div>
    </div>

    {{-- Filter Dropdown Lokasi/Lantai Toilet & Statistik Ringkasan --}}
    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_auto] items-end">
        @if($floors->isNotEmpty())
            <div class="max-w-md">
                <label for="floor_id_select" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">
                    Pilih Lokasi / Lantai Toilet
                </label>
                <div class="relative">
                    <select id="floor_id_select" onchange="window.location.href=this.value"
                            class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-4 py-2.5 pr-10 text-sm font-bold text-slate-800 shadow-sm focus:border-primary focus:ring-1 focus:ring-primary transition cursor-pointer">
                        @foreach($floors as $floorOption)
                            <option value="{{ route('duty.toilet.index', ['floor_id' => $floorOption->id]) }}" @selected($floor?->id === $floorOption->id)>
                                {{ $floorOption->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        @endif

        {{-- Statistik Pelaksanaan Hari Ini di Lantai Ini --}}
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm">
                <span>Total Terjadwal:</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-900">{{ $assignments->count() }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-800 shadow-sm">
                <span>✓ Sudah Membersihkan:</span>
                <span class="rounded-full bg-emerald-200 px-2 py-0.5 text-emerald-900">{{ $completedCount }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-800 shadow-sm">
                <span>Belum Melaksanakan:</span>
                <span class="rounded-full bg-amber-200 px-2 py-0.5 text-amber-900">{{ $pendingCount }}</span>
            </span>
        </div>
    </div>

    {{-- Form Pencatatan Siswa yang Sudah Membersihkan Toilet --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Catat Siswa yang Sudah Membersihkan Toilet</h2>
                <p class="mt-0.5 text-xs text-slate-500">Ketik nomor NIS siswa yang telah selesai bersih-bersih toilet jam 12 ini. Baik siswa terjadwal maupun pengganti akan otomatis tercatat "Sudah Membersihkan".</p>
            </div>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                Pencatatan Jam 12
            </span>
        </div>

        <form id="record-cleaning-form" action="{{ route('duty.toilet.record') }}" method="POST" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <input type="hidden" name="floor_id" value="{{ $floor?->id }}">

            <div>
                <label for="nis" class="mb-1 block text-sm font-semibold text-slate-700">NIS Siswa *</label>
                <input id="nis" name="nis" type="text" maxlength="20" value="{{ old('nis') }}" required
                       placeholder="Ketik NIS siswa..."
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                @error('nis') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="student-name" class="mb-1 block text-sm font-semibold text-slate-700">Nama Siswa</label>
                <input id="student-name" type="text" readonly placeholder="Otomatis terisi..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm font-medium text-slate-800">
            </div>

            <div>
                <label for="student-detail" class="mb-1 block text-sm font-semibold text-slate-700">Rombel & Rayon</label>
                <input id="student-detail" type="text" readonly placeholder="Otomatis terisi..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm font-medium text-slate-800">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-forest transition flex items-center justify-center gap-1.5">
                    <span>✓ Catat Sudah Selesai</span>
                </button>
            </div>

            <p id="student-lookup-message" class="text-xs text-slate-500 sm:col-span-2 lg:col-span-4" aria-live="polite"></p>
        </form>
    </div>

    {{-- Tabel Daftar Siswa Piket di Lantai Ini --}}
    <section class="mt-8">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Daftar Pelaksanaan Piket di {{ $floor?->name ?? 'Lantai Ini' }}</h2>
                <p class="text-xs text-slate-500">Klik tombol "Tandai Selesai" untuk menandai siswa yang telah membersihkan toilet.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ $assignments->count() }} Siswa
            </span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-4 py-3.5">Nama Siswa</th>
                        <th class="px-4 py-3.5">NIS</th>
                        <th class="px-4 py-3.5">Rombel</th>
                        <th class="px-4 py-3.5">Rayon</th>
                        <th class="px-4 py-3.5">Status Pelaksanaan Jam 12</th>
                        <th class="px-4 py-3.5 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assignments as $index => $item)
                        @php($isDone = $item->attendance && $item->attendance->status === 'present')
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-900">{{ $item->user->name }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-700">{{ $item->user->nis ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-700">
                                @if($item->user->classroom)
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                        {{ $item->user->classroom->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $item->user->rayon ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                @if($isDone)
                                    <div class="flex flex-col items-start gap-0.5">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                            <span>✓</span> Sudah Membersihkan
                                        </span>
                                        <span class="text-[11px] text-slate-400">
                                            Dicatat: {{ $item->attendance->created_at->format('H:i') }} WIB
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                        <span>•</span> Belum Melaksanakan
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <form action="{{ route('duty.toilet.toggle', $item) }}" method="POST">
                                    @csrf
                                    @if($isDone)
                                        <button type="submit" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 transition" title="Batalkan status sudah membersihkan">
                                            Batal
                                        </button>
                                    @else
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                                            ✓ Tandai Selesai
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                Belum ada siswa yang dijadwalkan piket toilet di lantai ini hari ini. Masukkan NIS siswa pada form di atas untuk mencatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <script>
        const nisInput = document.getElementById('nis');
        const nameInput = document.getElementById('student-name');
        const detailInput = document.getElementById('student-detail');
        const lookupMessage = document.getElementById('student-lookup-message');
        const lookupUrl = @json(route('duty.toilet.student-lookup'));
        let lookupController;

        nisInput.addEventListener('input', async () => {
            nameInput.value = '';
            detailInput.value = '';

            if (lookupController) {
                lookupController.abort();
            }

            if (!nisInput.value.trim() || nisInput.value.trim().length < 3) {
                lookupMessage.textContent = '';
                return;
            }

            lookupController = new AbortController();
            lookupMessage.textContent = 'Mencari data siswa...';

            try {
                const response = await fetch(`${lookupUrl}?nis=${encodeURIComponent(nisInput.value.trim())}`, {
                    headers: { Accept: 'application/json' },
                    signal: lookupController.signal,
                });
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Data siswa tidak ditemukan.');
                }

                nameInput.value = result.name;
                detailInput.value = `${result.classroom} · ${result.rayon || 'Rayon -'}`;
                lookupMessage.textContent = '✓ Data siswa ditemukan.';
                lookupMessage.className = 'text-xs text-emerald-600 font-medium sm:col-span-2 lg:col-span-4';
            } catch (error) {
                if (error.name !== 'AbortError') {
                    lookupMessage.textContent = error instanceof Error
                        ? error.message
                        : 'Data siswa tidak dapat dimuat.';
                    lookupMessage.className = 'text-xs text-slate-400 sm:col-span-2 lg:col-span-4';
                }
            }
        });

        if (nisInput.value.trim()) {
            nisInput.dispatchEvent(new Event('input'));
        }
    </script>
@endsection
