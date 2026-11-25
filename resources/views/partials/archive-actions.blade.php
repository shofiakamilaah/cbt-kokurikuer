<div class="flex flex-wrap gap-2">
    <a href="{{ route('profile.index', ['type' => $type, 'date' => today()->toDateString()]) }}"
       class="rounded-lg border border-primary px-3 py-2 text-sm font-semibold text-primary">
        Arsip hari ini
    </a>
    <a href="{{ route('profile.export', ['type' => $type, 'date' => today()->toDateString()]) }}"
       class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white">
        Export Excel
    </a>
</div>
