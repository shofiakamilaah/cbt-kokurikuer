@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-3xl border border-white/70 bg-white shadow-[0_24px_70px_rgba(27,67,57,0.12)] md:grid-cols-[0.9fr_1.1fr]">
        <div class="hidden flex-col justify-between bg-gradient-to-br from-forest to-primary p-9 text-white md:flex">
            <div>
                <span class="brand-mark text-sm font-black">KW</span>
                <p class="mt-12 text-xs font-bold uppercase tracking-[0.18em] text-white/65">Ruang kokurikuler</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight text-white">Satu langkah kecil,<br>perubahan besar.</h1>
                <p class="mt-4 max-w-xs text-sm leading-6 text-white/75">Kelola kegiatan dan temukan peranmu dalam menjaga lingkungan sekolah.</p>
            </div>
            <p class="text-xs text-white/60">Kokurikuler Wikrama · {{ date('Y') }}</p>
        </div>

        <div class="auth-card border-0 p-6 shadow-none sm:p-9">
            <div class="mb-6">
                <p class="text-sm font-bold uppercase tracking-[0.14em] text-primary">Mulai berkontribusi</p>
                <h1 class="mt-2 text-3xl font-bold text-forest">Daftar sebagai siswa</h1>
                <p class="mt-2 text-sm text-slate-600">Lengkapi informasi berikut untuk membuat akun kokurikuler.</p>
            </div>

            <form class="grid gap-4 sm:grid-cols-2" action="{{ route('register.store') }}" method="POST">
                @csrf
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold" for="name">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold" for="nis">NIS</label>
                    <input id="nis" name="nis" type="text" value="{{ old('nis') }}" required maxlength="20" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('nis') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold" for="classroom_id">Rombel</label>
                    <select id="classroom_id" name="classroom_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Pilih rombel</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" data-grade="{{ $classroom->grade_level }}" @selected((string) old('classroom_id') === (string) $classroom->id)>{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                    @error('classroom_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-sm font-semibold" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold" for="password">Kata sandi</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold" for="password_confirmation">Ulangi kata sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <button type="submit" class="sm:col-span-2 rounded-lg bg-primary px-4 py-3 font-semibold text-white">Daftar</button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-primary">Masuk</a></p>
        </div>
    </div>
@endsection
