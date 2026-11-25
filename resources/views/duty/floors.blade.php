@extends('layouts.panel')

@section('title', 'Denah Lantai')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Denah Lantai</h1>
    <p class="mt-1 text-sm text-slate-600">Tekan ruangan untuk menandai pengantaran tempat sampah. Tekan lagi untuk membatalkan.</p>
    <div class="mt-4">
        @include('partials.archive-actions', ['type' => 'waste_deliveries'])
    </div>

    <div class="mt-5 flex flex-wrap gap-2">
        @foreach($floors as $listedFloor)
            <a href="{{ route('duty.floors.index', ['floor' => $listedFloor->id]) }}"
               @class([
                    'rounded-lg border px-4 py-2 text-sm font-semibold',
                    'border-primary bg-primary text-white' => $floor?->id === $listedFloor->id,
                    'border-slate-300 bg-white text-slate-700' => $floor?->id !== $listedFloor->id,
               ])>
                {{ $listedFloor->name }} ({{ $progress[$listedFloor->id]['delivered'] }}/{{ $progress[$listedFloor->id]['total'] }})
            </a>
        @endforeach
    </div>

    @if($floor)
        <section class="relative mt-6 min-h-[560px] overflow-hidden rounded-2xl border border-slate-300 bg-cream/60 p-4">
            <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 rounded-lg border-y border-dashed border-slate-400 bg-slate-100 py-2 text-center text-xs font-semibold uppercase tracking-widest text-slate-500">
                Koridor
            </div>
            @foreach($floor->rooms as $room)
                @php($delivered = in_array($room->id, $deliveredRoomIds, true))
                <form action="{{ route('duty.floors.toggle', $room) }}" method="POST"
                      class="absolute"
                      style="left: {{ $room->pos_x }}%; top: {{ $room->pos_y }}%; width: {{ $room->width }}%; height: {{ $room->height }}%;">
                    @csrf
                    <button type="submit"
                            @class([
                                'flex h-full w-full flex-col items-center justify-center rounded-lg border-2 px-1 text-center text-xs font-semibold shadow-sm',
                                'border-sage bg-sage/30 text-slate-900' => $delivered,
                                'border-primary/50 bg-white text-slate-800' => ! $delivered,
                            ])>
                        <span class="font-bold">{{ $room->name }}</span>
                        @if($delivered)
                            <span class="mt-1">✓ Sudah diantar</span>
                        @else
                            <span class="mt-1">Belum diantar</span>
                        @endif
                    </button>
                </form>
            @endforeach
        </section>
    @else
        <p class="mt-6 rounded-xl bg-white p-6 text-sm text-slate-600">Belum ada data lantai.</p>
    @endif
@endsection
