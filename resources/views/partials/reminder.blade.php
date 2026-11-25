@if($reminderAssignments->isNotEmpty())
    @foreach($reminderAssignments as $assignment)
        <div class="mb-5 rounded-xl border border-primary/30 bg-white px-5 py-4 shadow-sm">
            <p class="font-semibold text-primary">Pengingat tugas kokurikuler</p>
            <p class="mt-1 text-sm text-slate-700">
                {{ $assignment->task_label }} di {{ $assignment->location_name }} pada
                {{ $assignment->assignment_date->translatedFormat('l, d F Y') }}.
            </p>
        </div>
    @endforeach
@endif
