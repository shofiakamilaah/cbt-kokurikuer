<header class="site-header">
    <div @class(['app-navbar-inner', 'has-navigation' => auth()->check()])>
        <a href="{{ route('home') }}" class="site-brand flex shrink-0 items-center gap-3 font-bold">
            <span class="brand-mark text-sm font-black">KW</span>
            <span>Kokurikuler Wikrama</span>
        </a>

        @auth
            <nav class="panel-nav" aria-label="Navigasi panel">
                
                @if(auth()->user()->isTeacher())
                    <a href="{{ route('admin.dashboard') }}" @class(['panel-nav-link', 'active' => request()->routeIs('admin.dashboard')])>Dashboard</a>
                    <a href="{{ route('admin.assignments.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('admin.assignments.*')])>Penugasan</a>
                @elseif(auth()->user()->isStudent())
                    @if(auth()->user()->isOfficer())
                        <a href="{{ route('duty.dashboard') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.dashboard')])>Tugas Hari Ini</a>
                        <a href="{{ route('duty.attendances.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.attendances.*')])>Absensi Petugas</a>
                    @endif

                    @if(auth()->user()->hasCleaningAssignment())
                        <a href="{{ route('duty.floors.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.floors.*')])>Denah Lantai</a>
                        <a href="{{ route('duty.toilet.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.toilet.*')])>Piket Toilet</a>
                        <a href="{{ route('duty.sorted-wastes.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.sorted-wastes.*')])>Pemilahan Sampah</a>
                        <a href="{{ route('duty.collections.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.collections.*')])>Pengumpulan Limbah (Rabu)</a>
                        <a href="{{ route('duty.activities.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.activities.*')])>Catatan Kegiatan</a>
                    @elseif(auth()->user()->isToiletOfficer())
                        <a href="{{ route('duty.toilet.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.toilet.*')])>Piket Toilet</a>
                    @elseif(auth()->user()->isCanteenOfficer())
                        <a href="{{ route('duty.sales.index') }}" @class(['panel-nav-link', 'active' => request()->routeIs('duty.sales.*')])>Pencatatan Penjualan Kantin</a>
                    @endif
                @endif
            </nav>

            <div class="navbar-account">
                <a href="{{ route('profile.index') }}" class="inline-flex" aria-label="Profil {{ auth()->user()->name }}">
                    @if(auth()->user()->profile_photo_path)
                        <img src="{{ asset('storage/'.auth()->user()->profile_photo_path) }}" alt="" class="h-9 w-9 rounded-full object-cover">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-white">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </span>
                    @endif
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="site-cta text-sm font-semibold">Keluar</button>
                </form>
            </div>
        @else
            <div class="navbar-account">
                <a href="{{ route('login') }}" class="site-link">Masuk</a>
                <a href="{{ route('register') }}" class="site-cta text-sm font-semibold">Daftar</a>
            </div>
        @endauth
    </div>
</header>
