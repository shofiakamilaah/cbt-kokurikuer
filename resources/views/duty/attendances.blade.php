@extends('layouts.panel')

@section('title', 'Absensi Petugas')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Absensi Petugas</h1>
    <p class="mt-1 text-sm text-slate-600">{{ today()->translatedFormat('l, d F Y') }} · Rombel {{ auth()->user()->classroom?->name ?? '-' }}</p>
    @if($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('duty.attendances.store') }}" method="POST" class="mt-6">
        @csrf
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3">Siswa</th>
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Tugas</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-4 py-3">{{ $assignment->user->name }}</td>
                            <td class="px-4 py-3">{{ $assignment->user->nis ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $assignment->task_label }}</td>
                            <td class="px-4 py-3">
                                <select name="status[{{ $assignment->id }}]" class="rounded-lg border border-slate-300 px-3 py-2">
                                    @foreach($statusLabels as $status => $label)
                                        <option value="{{ $status }}" @selected(old('status.'.$assignment->id, $assignment->attendance?->status ?? 'present') === $status)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input name="note[{{ $assignment->id }}]" type="text" maxlength="200"
                                       value="{{ old('note.'.$assignment->id, $assignment->attendance?->note ?? '') }}"
                                       class="min-w-[180px] rounded-lg border border-slate-300 px-3 py-2">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada petugas untuk diabsen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->isNotEmpty())
            <button type="submit" class="mt-5 rounded-lg bg-primary px-5 py-3 font-semibold text-white">Simpan Absensi</button>
        @endif
    </form>
@endsection
