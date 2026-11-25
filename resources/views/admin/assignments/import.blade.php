@extends('layouts.panel')

@section('title', 'Unggah Penugasan')

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('admin.assignments.index') }}" class="text-sm font-semibold text-primary">&larr; Kembali ke penugasan</a>
        <h1 class="mt-4 text-2xl font-bold text-slate-900">Unggah Petugas {{ $taskLabel }}</h1>
        <p class="mt-2 text-sm text-slate-600">
            Rombel {{ $classroom->name }} · Semester {{ $semester->label }}.
            Berkas berisi daftar NIS siswa untuk tugas {{ $taskLabel }}.
        </p>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
            <a href="{{ route('admin.assignments.template', [
                'classroom_id' => $classroom->id,
                'semester_id' => $semester->id,
                'assignment_type' => $assignmentType,
            ]) }}" class="inline-block rounded-lg border border-primary px-4 py-2 text-sm font-semibold text-primary">
                Unduh template NIS
            </a>
            <form action="{{ route('admin.assignments.import.store') }}" method="POST" enctype="multipart/form-data" class="mt-5">
                @csrf
                <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                <input type="hidden" name="assignment_type" value="{{ $assignmentType }}">
                <label for="file" class="mb-2 block text-sm font-semibold">Berkas CSV atau TXT</label>
                <input id="file" name="file" type="file" accept=".csv,.txt" required class="block w-full rounded-lg border border-slate-300 p-2">
                @error('file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                <button type="submit" class="mt-5 w-full rounded-lg bg-primary px-4 py-3 font-semibold text-white">Unggah dan Pratinjau</button>
            </form>
        </div>
    </div>
@endsection
