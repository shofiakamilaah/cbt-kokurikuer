<?php

namespace Database\Seeders;

use App\Models\Canteen;
use Illuminate\Database\Seeder;

class CanteenSeeder extends Seeder
{
    public function run(): void
    {
        $canteens = ['Kantin Utama', 'IDS', 'Cafe Hotel', 'BDP'];

        foreach ($canteens as $canteen) {
            Canteen::firstOrCreate(['name' => $canteen]);
        }
    }
}
