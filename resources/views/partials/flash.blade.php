@if(session('success'))
    <div class="notice mb-5 border border-sage/50 bg-sage/15 px-4 py-3 text-sm font-semibold text-slate-800" role="alert">
        {{ session('success') }}
    </div>
@endif
