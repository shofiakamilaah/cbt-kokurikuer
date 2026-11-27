<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\DutySchedule;
use App\Models\SortedWaste;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('classrooms', 'name')],
            'grade_level' => ['required', 'integer', Rule::in([10, 11, 12])],
        ]);

        Classroom::create($validated);

        return back()->with('success', "Rombel {$validated['name']} berhasil ditambahkan.");
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        $name = $classroom->name;
        $studentCount = $classroom->users()->count();

        DB::transaction(function () use ($classroom): void {
            // Lepaskan siswa dari rombel ini (jadikan null agar akun siswa tidak ikut terhapus otomatis)
            User::where('classroom_id', $classroom->id)->update(['classroom_id' => null]);

            // Hapus jadwal piket rombel dan timbangan sampah terkait
            DutySchedule::where('classroom_id', $classroom->id)->delete();
            SortedWaste::where('classroom_id', $classroom->id)->delete();

            $classroom->delete();
        });

        $info = $studentCount > 0
            ? "Data rombel {$name} berhasil dihapus ({$studentCount} siswa di dalamnya dilepas dari rombel)."
            : "Data rombel {$name} berhasil dihapus.";

        return back()->with('success', $info);
    }
}
