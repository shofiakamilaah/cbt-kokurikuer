@extends('layouts.panel')

@section('title', 'Piket Toilet')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Piket Toilet</h1>
    <p class="mt-1 text-sm text-slate-600">Daftar siswa yang bertugas di {{ $floor?->name ?? 'lantai belum tersedia' }} hari ini.</p>
    <div class="mt-4">
        @include('partials.archive-actions', ['type' => 'toilet_duty'])
    </div>

    @if($floors->isNotEmpty())
        <form action="{{ route('duty.toilet.index') }}" method="GET" class="mt-5 flex flex-wrap items-end gap-3">
            <div>
                <label for="floor_id" class="mb-1 block text-sm font-semibold">Lantai piket toilet</label>
                <select id="floor_id" name="floor_id" class="rounded-lg border border-slate-300 px-3 py-2">
                    @foreach($floors as $floorOption)
                        <option value="{{ $floorOption->id }}" @selected($floor?->id === $floorOption->id)>
                            {{ $floorOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 font-semibold text-white">Pilih lantai</button>
        </form>
    @endif

    <form id="replacement-form" action="{{ route('duty.toilet.replacement') }}" method="POST" class="mt-5 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
        @csrf
        <input type="hidden" name="floor_id" value="{{ $floor?->id }}">
        <div>
            <label for="nis" class="mb-1 block text-sm font-semibold">NIS siswa yang melaksanakan piket toilet</label>
            <input id="nis" name="nis" type="text" maxlength="20" value="{{ old('nis') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('nis') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="student-name" class="mb-1 block text-sm font-semibold">Nama</label>
            <input id="student-name" type="text" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2">
        </div>
        <div>
            <label for="student-rayon" class="mb-1 block text-sm font-semibold">Rayon</label>
            <input id="student-rayon" type="text" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2">
        </div>
        <button type="submit" class="self-end rounded-lg bg-primary px-4 py-2 font-semibold text-white">Catat Siswa Piket</button>
        <p id="student-lookup-message" class="text-sm text-slate-600 sm:col-span-2 lg:col-span-4" aria-live="polite"></p>
    </form>

    <section class="mt-6">
        <h2 class="text-xl font-bold text-slate-900">Daftar Siswa Piket Toilet Hari Ini</h2>
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Rayon</th>
                        <th class="px-4 py-3">Rombel</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-4 py-3">{{ $assignment->user->name }}</td>
                            <td class="px-4 py-3">{{ $assignment->user->nis ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $assignment->user->rayon ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $assignment->user->classroom?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada siswa yang tercatat di lantai ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <script>
        const nisInput = document.getElementById('nis');
        const nameInput = document.getElementById('student-name');
        const rayonInput = document.getElementById('student-rayon');
        const lookupMessage = document.getElementById('student-lookup-message');
        const lookupUrl = @json(route('duty.toilet.student-lookup'));
        let lookupController;

        nisInput.addEventListener('change', async () => {
            nameInput.value = '';
            rayonInput.value = '';

            if (lookupController) {
                lookupController.abort();
            }

            if (!nisInput.value.trim()) {
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
                    throw new Error(result.message || 'Data siswa tidak dapat ditemukan.');
                }

                nameInput.value = result.name;
                rayonInput.value = result.rayon || '';
                lookupMessage.textContent = result.rayon
                    ? 'Data siswa ditemukan.'
                    : 'Data siswa ditemukan, tetapi Rayon belum diatur.';
            } catch (error) {
                if (error.name !== 'AbortError') {
                    lookupMessage.textContent = error instanceof Error
                        ? error.message
                        : 'Data siswa tidak dapat dimuat.';
                }
            }
        });

        if (nisInput.value.trim()) {
            nisInput.dispatchEvent(new Event('change'));
        }
    </script>
@endsection
