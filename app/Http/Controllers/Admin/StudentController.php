<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\CanteenSale;
use App\Models\Classroom;
use App\Models\DutyAssignment;
use App\Models\SortedWaste;
use App\Models\User;
use App\Models\WasteDelivery;
use App\Models\WednesdayCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:20', 'unique:users,nis'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'rayon' => ['nullable', 'string', 'max:50'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'assignment_type' => ['nullable', 'string', Rule::in(['cleaning', 'canteen'])],
        ]);

        // Jika email kosong, buat email otomatis berdasarkan NIS
        $email = ! empty($validated['email'])
            ? $validated['email']
            : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $validated['nis'])).'@smkwikrama.sch.id';

        // Jika password kosong, gunakan password default 'password123'
        $password = ! empty($validated['password'])
            ? Hash::make($validated['password'])
            : Hash::make('password123');

        DB::transaction(function () use ($validated, $email, $password, $request): void {
            $student = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => $password,
                'role' => 'student',
                'nis' => $validated['nis'],
                'classroom_id' => $validated['classroom_id'],
                'rayon' => $validated['rayon'] ?? null,
            ]);

            // Jika guru langsung menetapkan tugas kebersihan / kantin
            if (! empty($validated['assignment_type']) && ! empty($validated['semester_id'])) {
                Assignment::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester_id' => $validated['semester_id'],
                    ],
                    [
                        'assignment_type' => $validated['assignment_type'],
                        'created_by' => $request->user()->id,
                    ]
                );

                \App\Services\DutyAssignmentSyncService::syncForStudent($student);
            }
        });

        return back()->with('success', "Data siswa {$validated['name']} (NIS: {$validated['nis']}) berhasil ditambahkan.");
    }

    public function destroy(User $student): RedirectResponse
    {
        // Pastikan hanya akun siswa yang dapat dihapus, bukan akun guru
        if (! $student->isStudent()) {
            return back()->with('error', 'Hanya akun siswa yang dapat dihapus.');
        }

        if ($student->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $name = $student->name;
        $nis = $student->nis ? " ({$student->nis})" : '';
        $teacherId = auth()->id();

        DB::transaction(function () use ($student, $teacherId): void {
            // 1. Hapus file foto profil jika ada
            if ($student->profile_photo_path && Storage::disk('public')->exists($student->profile_photo_path)) {
                Storage::disk('public')->delete($student->profile_photo_path);
            }

            // 2. Alihkan foreign key pencatat/pembuat ke guru yang sedang login
            Attendance::where('recorded_by', $student->id)->update(['recorded_by' => $teacherId]);
            DutyAssignment::where('created_by', $student->id)->update(['created_by' => $teacherId]);
            Assignment::where('created_by', $student->id)->update(['created_by' => $teacherId]);

            // 3. Hapus seluruh data riwayat yang dimiliki siswa ini
            DutyAssignment::where('user_id', $student->id)->delete();
            Assignment::where('student_id', $student->id)->delete();
            WednesdayCollection::where('contributor_id', $student->id)->orWhere('user_id', $student->id)->delete();
            WasteDelivery::where('user_id', $student->id)->delete();
            ActivityLog::where('user_id', $student->id)->delete();
            SortedWaste::where('user_id', $student->id)->delete();
            CanteenSale::where('user_id', $student->id)->delete();

            // 4. Hapus user siswa
            $student->delete();
        });

        return back()->with('success', "Data siswa {$name}{$nis} berhasil dihapus.");
    }
}
