<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar rombel kelas 10 saja:
        // PPLG 1-4, DKV 1-3, TJKT 1-4, PMN 1-2, MPLB 1, KLN 1-2, HTL 1-2
        $classrooms = [
            ['name' => 'PPLG X-1', 'grade_level' => 10],
            ['name' => 'PPLG X-2', 'grade_level' => 10],
            ['name' => 'PPLG X-3', 'grade_level' => 10],
            ['name' => 'PPLG X-4', 'grade_level' => 10],
            ['name' => 'DKV X-1', 'grade_level' => 10],
            ['name' => 'DKV X-2', 'grade_level' => 10],
            ['name' => 'DKV X-3', 'grade_level' => 10],
            ['name' => 'TJKT X-1', 'grade_level' => 10],
            ['name' => 'TJKT X-2', 'grade_level' => 10],
            ['name' => 'TJKT X-3', 'grade_level' => 10],
            ['name' => 'TJKT X-4', 'grade_level' => 10],
            ['name' => 'PMN X-1', 'grade_level' => 10],
            ['name' => 'PMN X-2', 'grade_level' => 10],
            ['name' => 'MPLB X-1', 'grade_level' => 10],
            ['name' => 'KLN X-1', 'grade_level' => 10],
            ['name' => 'KLN X-2', 'grade_level' => 10],
            ['name' => 'HTL X-1', 'grade_level' => 10],
            ['name' => 'HTL X-2', 'grade_level' => 10],
        ];

        Schema::disableForeignKeyConstraints();
        Classroom::truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($classrooms as $c) {
            Classroom::create($c);
        }
    }
}
