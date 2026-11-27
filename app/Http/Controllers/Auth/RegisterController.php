<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:20'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $existingStudent = User::where('nis', $validated['nis'])
            ->where('role', 'student')
            ->first();

        // JIKA NIS SUDAH PERNAH DIINPUT OLEH GURU:
        // Siswa tidak diblokir, melainkan akun tersebut langsung diaktivasi / diklaim oleh siswa!
        if ($existingStudent) {
            $emailTaken = User::where('email', $validated['email'])
                ->where('id', '!=', $existingStudent->id)
                ->exists();

            if ($emailTaken) {
                return back()->withErrors(['email' => 'Email ini sudah digunakan oleh akun lain.'])->withInput();
            }

            $existingStudent->update([
                'name' => $validated['name'],
                'classroom_id' => $validated['classroom_id'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Akun kamu berhasil diaktivasi! Tugas yang telah diatur oleh guru otomatis tersambung. Silakan login.');
        }

        // JIKA NIS BELUM ADA: Buat akun baru seperti biasa
        if (User::where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => 'Email ini sudah terdaftar.'])->withInput();
        }

        User::create([
            'name' => $validated['name'],
            'nis' => $validated['nis'],
            'classroom_id' => $validated['classroom_id'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silakan masuk menggunakan akun Anda.');
    }
}
