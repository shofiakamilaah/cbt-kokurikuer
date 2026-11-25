<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ClassroomSeeder::class);

        User::updateOrCreate(
            ['email' => 'guru@example.com'],
            [
                'name' => 'Guru',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
                'nis' => null,
                'classroom_id' => null,
            ],
        );

        User::updateOrCreate(
            ['email' => 'siswa@example.com'],
            [
                'name' => 'Siswa',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'nis' => null,
                'classroom_id' => null,
            ],
        );

        User::updateOrCreate(
            ['email' => 'guru@wikrama.test'],
            [
                'name' => 'Guru Demo',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
                'nis' => null,
                'classroom_id' => null,
            ],
        );

        $demoStudents = [
            [
                'email' => 'cleaning@wikrama.test',
                'name' => 'Petugas Kebersihan',
                'nis' => '12610001',
                'classroom' => 'PPLG X-1',
            ],
            [
                'email' => 'toilet@wikrama.test',
                'name' => 'Petugas Toilet',
                'nis' => '12610002',
                'classroom' => 'PPLG X-1',
            ],
            [
                'email' => 'canteen@wikrama.test',
                'name' => 'Petugas Kantin',
                'nis' => '12610019',
                'classroom' => 'PPLG X-1',
            ],
            [
                'email' => 'toilet-duty@wikrama.test',
                'name' => 'Siswa Piket Toilet',
                'nis' => '12640001',
                'classroom' => 'TJKT X-1',
            ],
            [
                'email' => 'regular@wikrama.test',
                'name' => 'Siswa Reguler',
                'nis' => '12640002',
                'classroom' => 'TJKT X-1',
            ],
            [
                'email' => 'grade11@wikrama.test',
                'name' => 'Siswa Kelas XI',
                'nis' => '12611001',
                'classroom' => 'PPLG XI-1',
            ],
        ];

        foreach ($demoStudents as $demoStudent) {
            $classroom = Classroom::where('name', $demoStudent['classroom'])->firstOrFail();

            User::updateOrCreate(
                ['email' => $demoStudent['email']],
                [
                    'name' => $demoStudent['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'student',
                    'nis' => $demoStudent['nis'],
                    'classroom_id' => $classroom->id,
                ],
            );
        }
    }
}
