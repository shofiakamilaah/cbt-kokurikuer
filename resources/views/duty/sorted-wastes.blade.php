@extends('layouts.panel')

@section('title', 'Pemilahan Sampah')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Pemilahan Sampah</h1>
    <p class="mt-1 text-sm text-slate-600">Catat hasil pemilahan rombel kamu hari ini dalam kilogram.</p>
    <div class="mt-4">
        @include('partials.archive-actions', ['type' => 'sorted_wastes'])
    </div>

    <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <div>
            <form action="{{ route('duty.sorted-wastes.store') }}" method="POST" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
                @csrf
                @foreach([
                    'organic_weight' => 'Organik (kg)',
                    'inorganic_weight' => 'Non-organik (kg)',
                    'residue_weight' => 'Residu (kg)',
                ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-1 block text-sm font-semibold">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="number" step="0.01" min="0" max="999"
                               value="{{ old($field, $sortedWaste?->$field ?? '') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
                <button type="submit" class="rounded-lg bg-primary px-5 py-3 font-semibold text-white">Simpan Berat Sampah</button>
            </form>

            @if($sortedWaste)
                <p class="mt-4 text-sm text-slate-600">Data hari ini tersimpan. Menyimpan kembali akan memperbarui data yang sama.</p>
            @endif
        </div>

        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-bold text-slate-900">Riwayat Pemilahan</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Organik</th>
                            <th class="px-3 py-2">Non-organik</th>
                            <th class="px-3 py-2">Residu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $entry)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="px-3 py-2">{{ $entry->sorted_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $entry->organic_weight, 2, ',', '.') }} kg</td>
                                <td class="px-3 py-2">{{ number_format((float) $entry->inorganic_weight, 2, ',', '.') }} kg</td>
                                <td class="px-3 py-2">{{ number_format((float) $entry->residue_weight, 2, ',', '.') }} kg</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">Belum ada riwayat pemilahan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
