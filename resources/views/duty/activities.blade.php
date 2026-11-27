@extends('layouts.panel')

@section('title', 'Catatan Kegiatan Kokurikuler')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-primary">{{ today()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">Catatan Kegiatan Kokurikuler</h1>
            <p class="mt-1 text-sm text-slate-600">Dokumentasikan aktivitas kokurikuler rombel kamu hari ini beserta foto kegiatan.</p>
        </div>
        <div>
            @include('partials.archive-actions', ['type' => 'activity_logs'])
        </div>
    </div>

    {{-- Form Tambah Kegiatan --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Input Kegiatan Baru</h2>
        <p class="mt-0.5 text-xs text-slate-500">Pilih jenis kegiatan, lokasi lantai, deskripsi singkat, dan unggah foto dokumentasi.</p>

        <form action="{{ route('duty.activities.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf

            <div>
                <label for="activity_type" class="mb-1 block text-sm font-semibold text-slate-700">Jenis Kegiatan</label>
                <select id="activity_type" name="activity_type" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    @foreach($activityLabels as $activityType => $activityLabel)
                        <option value="{{ $activityType }}" @selected(old('activity_type') === $activityType)>{{ $activityLabel }}</option>
                    @endforeach
                </select>
                @error('activity_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="floor_id" class="mb-1 block text-sm font-semibold text-slate-700">Lokasi Lantai (opsional)</label>
                <select id="floor_id" name="floor_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">Tidak ditentukan / Seluruh area</option>
                    @foreach($floors as $floor)
                        <option value="{{ $floor->id }}" @selected((string) old('floor_id') === (string) $floor->id)>{{ $floor->name }}</option>
                    @endforeach
                </select>
                @error('floor_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="mb-1 block text-sm font-semibold text-slate-700">Deskripsi / Keterangan Kegiatan (opsional)</label>
                <textarea id="description" name="description" rows="3" maxlength="500"
                          placeholder="Jelaskan secara singkat apa saja yang dikerjakan hari ini..."
                          class="w-full rounded-xl border border-slate-300 p-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="photo" class="mb-1 block text-sm font-semibold text-slate-700">Foto Dokumentasi (opsional, maks 4 MB)</label>
                <input id="photo" name="photo" type="file" accept="image/*"
                       class="block w-full rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary hover:file:bg-primary/20">
                @error('photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 font-semibold text-white shadow hover:bg-forest transition">
                    + Simpan Catatan Kegiatan
                </button>
            </div>
        </form>
    </div>

    {{-- Kegiatan Hari Ini --}}
    <section class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Kegiatan Rombel Hari Ini</h2>
            <span class="rounded-full bg-olive/30 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ $activityLogs->count() }} Kegiatan
            </span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @forelse($activityLogs as $activityLog)
                <article class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-primary/40 transition">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <span class="inline-flex rounded-full bg-primary/10 px-2.5 py-1 text-xs font-bold text-primary">
                                {{ $activityLog->type_label }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">
                                {{ $activityLog->created_at->format('H:i') }} WIB
                            </span>
                        </div>

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            {{ $activityLog->floor?->name ?? 'Semua Lantai' }} &bull; {{ $activityLog->user->name }}
                        </p>

                        @if($activityLog->description)
                            <p class="mt-3 text-sm text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50/80 p-3 rounded-xl border border-slate-100">
                                {{ $activityLog->description }}
                            </p>
                        @endif
                    </div>

                    @if($activityLog->photo)
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-500 flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Ada Foto
                            </span>
                            <a href="{{ asset('storage/'.$activityLog->photo) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary hover:bg-primary hover:text-white transition">
                                Buka Foto Dokumentasi &rarr;
                            </a>
                        </div>
                    @endif
                </article>
            @empty
                <div class="sm:col-span-2 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-400">
                    <svg class="mx-auto h-10 w-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Belum ada catatan kegiatan hari ini.<br>
                    <span class="text-xs text-slate-400">Silakan gunakan formulir di atas untuk mencatat kegiatan rombel kamu.</span>
                </div>
            @endforelse
        </div>
    </section>
@endsection
