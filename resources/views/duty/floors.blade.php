@extends('layouts.panel')

@section('title', 'Denah Lantai - Pengantaran Tempat Sampah')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-primary">{{ today()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">Denah Lantai & Pengantaran Tempat Sampah</h1>
            <p class="mt-1 text-sm text-slate-600">Tekan tombol ruangan untuk menandai tempat sampah yang sudah diantar. Tekan kembali untuk membatalkan.</p>
        </div>
        <div>
            @include('partials.archive-actions', ['type' => 'waste_deliveries'])
        </div>
    </div>

    {{-- Tabs Lantai --}}
    <div class="mt-6 flex flex-wrap items-center gap-2.5">
        @foreach($floors as $listedFloor)
            @php($p = $progress[$listedFloor->id] ?? ['delivered' => 0, 'total' => 0])
            @php($isCurrent = $floor?->id === $listedFloor->id)
            <a href="{{ route('duty.floors.index', ['floor' => $listedFloor->id]) }}"
               @class([
                    'flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition shadow-sm',
                    'border-primary bg-primary text-white shadow-md' => $isCurrent,
                    'border-slate-200 bg-white text-slate-700 hover:border-primary/40 hover:bg-slate-50' => ! $isCurrent,
               ])>
                <span>{{ $listedFloor->name }}</span>
                <span @class([
                    'rounded-full px-2 py-0.5 text-xs',
                    'bg-white/20 text-white' => $isCurrent,
                    'bg-slate-100 text-slate-600' => ! $isCurrent,
                ])>
                    {{ $p['delivered'] }} / {{ $p['total'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Area Denah --}}
    @if($floor)
        @php($currentProgress = $progress[$floor->id] ?? ['delivered' => 0, 'total' => 0])
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $floor->name }}</h2>
                    <p class="text-xs text-slate-500">Klik pada kotak ruangan untuk mengubah status pengantaran.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                        <span class="h-3 w-3 rounded-md border border-primary/50 bg-white shadow-xs"></span>
                        Belum Diantar
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                        <span class="h-3 w-3 rounded-md border-2 border-emerald-500 bg-emerald-100"></span>
                        Sudah Diantar
                    </span>
                </div>
            </div>

            <section class="relative mt-6 min-h-[580px] overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-b from-cream/40 via-white to-cream/30 p-4 shadow-inner">
                {{-- Strip Koridor Tengah --}}
                <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 flex items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-100/90 py-2.5 text-xs font-bold uppercase tracking-widest text-slate-400 shadow-xs">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                        Area Koridor {{ $floor->name }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </span>
                </div>

                {{-- Kotak Ruangan --}}
                @foreach($floor->rooms as $room)
                    @php($delivered = in_array($room->id, $deliveredRoomIds, true))
                    <form action="{{ route('duty.floors.toggle', $room) }}" method="POST"
                          class="absolute transition-transform active:scale-95"
                          style="left: {{ $room->pos_x }}%; top: {{ $room->pos_y }}%; width: {{ $room->width }}%; height: {{ $room->height }}%;">
                        @csrf
                        <button type="submit"
                                @class([
                                    'group flex h-full w-full flex-col items-center justify-center rounded-xl border-2 px-2 py-1 text-center transition shadow-sm',
                                    'border-emerald-500 bg-emerald-50 text-emerald-950 shadow-emerald-100' => $delivered,
                                    'border-slate-300 bg-white text-slate-800 hover:border-primary hover:shadow-md' => ! $delivered,
                                ])>
                            <span class="font-extrabold text-xs sm:text-sm text-slate-900 group-hover:text-primary transition">{{ $room->name }}</span>
                            @if($delivered)
                                <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Selesai
                                </span>
                            @else
                                <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 group-hover:bg-primary/10 group-hover:text-primary transition">
                                    Belum Diantar
                                </span>
                            @endif
                        </button>
                    </form>
                @endforeach
            </section>
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
            Belum ada data lantai yang terdaftar.
        </div>
    @endif
@endsection
