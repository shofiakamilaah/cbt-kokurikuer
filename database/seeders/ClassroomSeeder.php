<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $majors = ['PPLG', 'DKV', 'TJKT', 'BDP'];

        foreach ($majors as $major) {
            // Grade 10: X-1 to X-3
            for ($section = 1; $section <= 3; $section++) {
                Classroom::updateOrCreate(
                    ['name' => "{$major} X-{$section}"],
                    ['grade_level' => 10],
                );
            }

            // Grade 11: XI-1, XI-2
            for ($section = 1; $section <= 2; $section++) {
                Classroom::updateOrCreate(
                    ['name' => "{$major} XI-{$section}"],
                    ['grade_level' => 11],
                );
            }

            // Grade 12: XII-1, XII-2
            for ($section = 1; $section <= 2; $section++) {
                Classroom::updateOrCreate(
                    ['name' => "{$major} XII-{$section}"],
                    ['grade_level' => 12],
                );
            }
        }
    }
}
