@extends('layouts.panel')

@section('title', 'Pratinjau Impor')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pratinjau Petugas {{ $taskLabel }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $classroom->name }} · {{ $semester->label }}</p>
        </div>
        <a href="{{ route('admin.assignments.index') }}" class="text-sm font-semibold text-primary">Kembali ke rombel</a>
    </div>

    @if(($counts['error'] ?? 0) > 0)
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $counts['error'] }} baris memiliki kesalahan dan tidak dapat diunggah.
        </div>
    @endif

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">NIS</th>
                    <th class="px-4 py-3">Siswa</th>
                    <th class="px-4 py-3">Tugas</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Pesan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3">{{ $row['nis'] }}</td>
                        <td class="px-4 py-3">{{ $row['nama'] }}</td>
                        <td class="px-4 py-3">{{ $row['tugas'] }}</td>
                        <td class="px-4 py-3">{{ ucfirst($row['status']) }}</td>
                        <td class="px-4 py-3">{{ $row['message'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Berkas belum berisi data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form action="{{ route('admin.assignments.import.confirm') }}" method="POST" class="mt-6">
        @csrf
        <button type="submit" @disabled($counts['error'] > 0 || count($rows) === 0) class="rounded-lg bg-primary px-5 py-3 font-semibold text-white disabled:cursor-not-allowed disabled:bg-slate-300">
            Konfirmasi Penugasan
        </button>
    </form>
@endsection
