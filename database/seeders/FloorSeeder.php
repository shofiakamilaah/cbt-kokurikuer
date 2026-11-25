<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    public function run(): void
    {
        for ($level = 1; $level <= 4; $level++) {
            $floor = Floor::updateOrCreate(
                ['name' => "Lantai {$level}"],
                ['level' => $level],
            );

            for ($index = 0; $index < 8; $index++) {
                $column = $index % 4;
                $bottom = $index >= 4;

                Room::updateOrCreate(
                    [
                        'floor_id' => $floor->id,
                        'name' => 'Ruang '.($index + 1),
                    ],
                    [
                        'pos_x' => 5 + $column * 23,
                        'pos_y' => $bottom ? 58 : 8,
                        'width' => 20,
                        'height' => 34,
                    ],
                );
            }
        }
    }
}
