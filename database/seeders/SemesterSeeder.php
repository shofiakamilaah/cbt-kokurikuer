<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        Semester::updateOrCreate(
            ['academic_year' => '2026/2027', 'semester_number' => 1],
            ['start_date' => '2026-07-13', 'end_date' => '2026-12-19'],
        );

        Semester::updateOrCreate(
            ['academic_year' => '2026/2027', 'semester_number' => 2],
            ['start_date' => '2027-01-04', 'end_date' => '2027-06-19'],
        );
    }
}
