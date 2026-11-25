<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            ClassroomSeeder::class,
            SemesterSeeder::class,
            FloorSeeder::class,
            CanteenSeeder::class,
            UserSeeder::class,
            DemoAssignmentSeeder::class,
            DemoCollectionSeeder::class,
            DemoSortedWasteSeeder::class,
        ]);
    }
}
