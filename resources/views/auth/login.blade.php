@extends('layouts.app')

@section('title', 'Masuk')

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
            <div class="mb-8">
                <p class="text-sm font-bold uppercase tracking-[0.14em] text-primary">Selamat datang</p>
                <h1 class="mt-2 text-3xl font-bold text-forest">Masuk ke akun</h1>
                <p class="mt-2 text-sm text-slate-600">Gunakan email dan kata sandi yang sudah terdaftar.</p>
            </div>
            <form class="space-y-5" action="{{ route('login.store') }}" method="POST">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-semibold" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold" for="password">Kata sandi</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 font-semibold text-white">Masuk</button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-primary">Daftar sebagai siswa</a></p>
        </div>
    </div>
@endsection
