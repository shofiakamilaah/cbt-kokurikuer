@extends('layouts.panel')

@section('title', 'Catatan Kegiatan')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Catatan Kegiatan</h1>
    <p class="mt-1 text-sm text-slate-600">Catat kegiatan rombel kamu hari ini.</p>
    <div class="mt-4">
        @include('partials.archive-actions', ['type' => 'activity_logs'])
    </div>

    <form action="{{ route('duty.activities.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 grid gap-5 rounded-xl border border-slate-200 bg-white p-6 md:grid-cols-2">
        @csrf
        <div>
            <label for="activity_type" class="mb-1 block text-sm font-semibold">Jenis kegiatan</label>
            <select id="activity_type" name="activity_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach($activityLabels as $activityType => $activityLabel)
                    <option value="{{ $activityType }}" @selected(old('activity_type') === $activityType)>{{ $activityLabel }}</option>
                @endforeach
            </select>
            @error('activity_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="floor_id" class="mb-1 block text-sm font-semibold">Lantai (opsional)</label>
            <select id="floor_id" name="floor_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="">Tidak dipilih</option>
                @foreach($floors as $floor)
                    <option value="{{ $floor->id }}" @selected((string) old('floor_id') === (string) $floor->id)>{{ $floor->name }}</option>
                @endforeach
            </select>
            @error('floor_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label for="description" class="mb-1 block text-sm font-semibold">Keterangan (opsional)</label>
            <textarea id="description" name="description" rows="3" maxlength="500" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('description') }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label for="photo" class="mb-1 block text-sm font-semibold">Foto (opsional, maksimal 4 MB)</label>
            <input id="photo" name="photo" type="file" accept="image/*" class="block w-full rounded-lg border border-slate-300 p-2">
            @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="rounded-lg bg-primary px-5 py-3 font-semibold text-white md:col-span-2">Simpan Kegiatan</button>
    </form>

    <section class="mt-8">
        <h2 class="text-xl font-bold text-slate-900">Kegiatan Hari Ini</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse($activityLogs as $activityLog)
                <article class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-bold">{{ $activityLog->type_label }}</h3>
                        <span class="text-xs text-slate-500">{{ $activityLog->created_at->format('H:i') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">{{ $activityLog->floor?->name ?? 'Lantai tidak dicantumkan' }} · {{ $activityLog->user->name }}</p>
                    @if($activityLog->description)
                        <p class="mt-3 whitespace-pre-line text-sm">{{ $activityLog->description }}</p>
                    @endif
                    @if($activityLog->photo)
                        <a href="{{ asset('storage/'.$activityLog->photo) }}" target="_blank" rel="noopener" class="mt-3 inline-block text-sm font-semibold text-primary">Lihat foto</a>
                    @endif
                </article>
            @empty
                <p class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">Belum ada catatan kegiatan hari ini.</p>
            @endforelse
        </div>
    </section>
@endsection
