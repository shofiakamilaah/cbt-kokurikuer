@extends('layouts.panel')

@section('title', 'Pengumpulan Limbah')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Pengumpulan Limbah</h1>
    <p class="mt-1 text-sm text-slate-600">Cari siswa menggunakan minimal 3 digit NIS.</p>
    <div class="mt-4">
        @include('partials.archive-actions', ['type' => 'wednesday_collections'])
    </div>

    <form action="{{ route('duty.collections.index') }}" method="GET" class="mt-5 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-5">
        <div class="min-w-[220px] flex-1">
            <label for="keyword" class="mb-1 block text-sm font-semibold">Cari berdasarkan NIS</label>
            <input id="keyword" name="keyword" type="search" value="{{ $keyword }}" minlength="3" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('keyword') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 font-semibold text-white">Cari Siswa</button>
    </form>

    @if($studentMatches->isNotEmpty())
        <section class="mt-5">
            <h2 class="font-bold text-slate-900">Hasil pencarian</h2>
            <div class="mt-3 grid gap-3 md:grid-cols-2">
                @foreach($studentMatches as $student)
                    <article class="rounded-xl border border-slate-200 bg-white p-5">
                        <div>
                            <h3 class="font-bold">{{ $student->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">NIS {{ $student->nis }} · {{ $student->classroom?->name ?? '-' }}</p>
                        </div>
                        <form action="{{ route('duty.collections.store') }}" method="POST" class="mt-4 grid gap-3 sm:grid-cols-2">
                            @csrf
                            <input type="hidden" name="contributor_id" value="{{ $student->id }}">
                            <div>
                                <label for="cooking_oil_amount-{{ $student->id }}" class="mb-1 block text-sm font-semibold">Minyak jelantah (liter)</label>
                                <input id="cooking_oil_amount-{{ $student->id }}" name="cooking_oil_amount" type="number" step="0.01" min="0.01" max="999" value="{{ old('cooking_oil_amount') }}" placeholder="Jumlah (opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @error('cooking_oil_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="eggshell_submitted-{{ $student->id }}" class="flex items-center gap-2 text-sm font-semibold">
                                    <input id="eggshell_submitted-{{ $student->id }}" name="eggshell_submitted" type="checkbox" value="1" @checked(old('eggshell_submitted')) class="h-4 w-4 rounded border-slate-300 text-primary">
                                    Setor cangkang telur
                                </label>
                                @error('eggshell_submitted') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white sm:col-span-2">Catat Pengumpulan</button>
                            @error('contributor_id') <p class="text-sm text-red-600 sm:col-span-2">{{ $message }}</p> @enderror
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    @elseif(request()->filled('keyword') && ! $errors->has('keyword'))
        <p class="mt-4 rounded-lg bg-white p-4 text-sm text-slate-600">Tidak ada siswa yang cocok dengan NIS tersebut.</p>
    @endif

    <section class="mt-8">
        <h2 class="text-xl font-bold text-slate-900">Entri Hari Ini</h2>
        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3">Penyetor</th>
                        <th class="px-4 py-3">Rombel</th>
                        <th class="px-4 py-3">Minyak jelantah</th>
                        <th class="px-4 py-3">Cangkang telur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $collection)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-4 py-3">{{ $collection['contributor']->name }}</td>
                            <td class="px-4 py-3">{{ $collection['contributor']->classroom?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                {{ $collection['cooking_oil_amount'] > 0 ? number_format($collection['cooking_oil_amount'], 2, ',', '.').' liter' : '-' }}
                            </td>
                            <td class="px-4 py-3">{{ $collection['has_eggshell'] ? 'Disetor' : 'Tidak setor' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada pengumpulan hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
