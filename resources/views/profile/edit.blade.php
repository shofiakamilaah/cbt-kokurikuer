@extends('layouts.panel')

@section('title', 'Edit Profil')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Edit Profil</h1>
        <a href="{{ route('profile.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
            Kembali ke profil
        </a>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
          class="mt-5 grid max-w-2xl gap-5 rounded-xl border border-slate-200 bg-white p-5">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-4">
            @if($profileUser->profile_photo_path)
                <img src="{{ asset('storage/'.$profileUser->profile_photo_path) }}" alt="Foto profil {{ $profileUser->name }}"
                     class="h-20 w-20 rounded-full object-cover">
            @else
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-primary text-2xl font-bold text-white">
                    {{ mb_substr($profileUser->name, 0, 1) }}
                </span>
            @endif
            <div class="flex-1">
                <label for="profile_photo" class="mb-1 block text-sm font-semibold">Foto profil</label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/*"
                       class="block w-full rounded-lg border border-slate-300 p-2">
                <p class="mt-1 text-xs text-slate-500">Format gambar, maksimal 2 MB.</p>
                @error('profile_photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama</label>
            <input id="name" name="name" type="text" maxlength="255" value="{{ old('name', $profileUser->name) }}" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
            <input id="email" name="email" type="email" maxlength="255" value="{{ old('email', $profileUser->email) }}" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="justify-self-start rounded-lg bg-primary px-5 py-3 font-semibold text-white">
            Simpan Perubahan
        </button>
    </form>
@endsection
