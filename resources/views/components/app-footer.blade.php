<footer class="mt-20 border-t border-slate-200/80 bg-white/90 backdrop-blur-md text-slate-700">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4">
            {{-- Kolom 1: Profil & Identitas --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="brand-mark text-sm font-black shadow-md">KW</span>
                    <div>
                        <span class="text-base font-bold text-forest block leading-tight">Kokurikuler Wikrama</span>
                        <span class="text-xs text-slate-500 font-medium">SMK Wikrama Bogor</span>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-slate-600">
                    Sistem pemantauan dan pencatatan kegiatan kokurikuler siswa untuk membentuk karakter peduli lingkungan, mandiri, disiplin, dan berbudaya kerja prima.
                </p>
                <div class="flex flex-wrap gap-2 pt-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sekolah Adiwiyata
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200">
                        🌱 Lingkungan Hidup
                    </span>
                </div>
            </div>

            {{-- Kolom 2: Program Kokurikuler --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-forest">Program Utama</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary shrink-0"></span>
                        <span>Piket Kebersihan Lingkungan & Rombel</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary shrink-0"></span>
                        <span>Pencatatan Piket Toilet (Jam 12:00 WIB)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary shrink-0"></span>
                        <span>Pelayanan & Kasir Kantin Sehat</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary shrink-0"></span>
                        <span>Pemilahan & Penimbangan Sampah</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary shrink-0"></span>
                        <span>Pengumpulan Limbah Minyak Jelantah</span>
                    </li>
                </ul>
            </div>

            {{-- Kolom 3: Navigasi Cepat --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-forest">Tautan Cepat</h3>
                <ul class="mt-4 space-y-2.5 text-xs text-slate-600">
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                            <span class="text-slate-400">→</span> Beranda Utama
                        </a>
                    </li>
                    @guest
                        <li>
                            <a href="{{ route('login') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                                <span class="text-slate-400">→</span> Masuk ke Akun
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                                <span class="text-slate-400">→</span> Pendaftaran Akun Siswa
                            </a>
                        </li>
                    @else
                        @if(auth()->user()->isTeacher())
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                                    <span class="text-slate-400">→</span> Dashboard Guru
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.assignments.index') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                                    <span class="text-slate-400">→</span> Kelola Penugasan
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('duty.dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                                    <span class="text-slate-400">→</span> Dashboard Tugas Siswa
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('profile.index') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                                <span class="text-slate-400">→</span> Profil Pengguna
                            </a>
                        </li>
                    @endguest
                </ul>
            </div>

            {{-- Kolom 4: Kontak & Informasi Sekolah --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-forest">SMK Wikrama Bogor</h3>
                <p class="mt-4 text-xs leading-relaxed text-slate-600">
                    Jl. Raya Wangun No. 21, Kel. Sindangsari, Kec. Bogor Timur, Kota Bogor, Jawa Barat 16146
                </p>
                <div class="mt-3.5 space-y-2 text-xs text-slate-600">
                    <p class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>prohumas@smkwikrama.sch.id</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        <a href="https://smkwikrama.sch.id" target="_blank" rel="noopener noreferrer" class="hover:text-primary transition-colors underline decoration-slate-300 underline-offset-2">
                            smkwikrama.sch.id
                        </a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Garis Pemisah & Baris Bawah --}}
        <div class="mt-12 border-t border-slate-200/80 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p>© {{ date('Y') }} SMK Wikrama Bogor. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-3">
                <span class="text-slate-400">Disiplin</span>
                <span class="text-slate-300">•</span>
                <span class="text-slate-400">Peduli Lingkungan</span>
                <span class="text-slate-300">•</span>
                <span class="text-slate-400">Berakhlak Mulia</span>
                <span class="text-slate-300">•</span>
                <a href="#top" class="text-primary font-semibold hover:underline flex items-center gap-1">
                    <span>Atas</span>
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>
