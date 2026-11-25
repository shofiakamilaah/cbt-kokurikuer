<?php

namespace Database\Seeders;

use App\Models\SortedWaste;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSortedWasteSeeder extends Seeder
{
    public function run(): void
    {
        $officer = User::where('email', 'cleaning@wikrama.test')->firstOrFail();
        $demoHistory = [
            [
                'sorted_date' => today()->subDays(1),
                'organic_weight' => 3.2,
                'inorganic_weight' => 1.1,
                'residue_weight' => 0.6,
            ],
            [
                'sorted_date' => today()->subDays(7),
                'organic_weight' => 4.1,
                'inorganic_weight' => 1.4,
                'residue_weight' => 0.8,
            ],
            [
                'sorted_date' => today()->subDays(14),
                'organic_weight' => 3.7,
                'inorganic_weight' => 1.2,
                'residue_weight' => 0.5,
            ],
        ];

        foreach ($demoHistory as $entry) {
            SortedWaste::updateOrCreate(
                [
                    'classroom_id' => $officer->classroom_id,
                    'sorted_date' => $entry['sorted_date'],
                ],
                [
                    'user_id' => $officer->id,
                    'organic_weight' => $entry['organic_weight'],
                    'inorganic_weight' => $entry['inorganic_weight'],
                    'residue_weight' => $entry['residue_weight'],
                ],
            );
        }
    }
}
