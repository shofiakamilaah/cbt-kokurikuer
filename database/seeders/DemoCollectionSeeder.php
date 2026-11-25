<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WednesdayCollection;
use Illuminate\Database\Seeder;

class DemoCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $cleaningOfficer = User::where('email', 'cleaning@wikrama.test')->firstOrFail();
        $collectionDate = today();

        $demoCollections = [
            [
                'contributor_email' => 'toilet@wikrama.test',
                'waste_type' => 'cooking_oil',
                'amount' => 1.5,
            ],
            [
                'contributor_email' => 'canteen@wikrama.test',
                'waste_type' => 'eggshell',
                'amount' => null,
            ],
        ];

        foreach ($demoCollections as $demoCollection) {
            $contributor = User::where('email', $demoCollection['contributor_email'])->firstOrFail();

            WednesdayCollection::updateOrCreate(
                [
                    'contributor_id' => $contributor->id,
                    'user_id' => $cleaningOfficer->id,
                    'collection_date' => $collectionDate,
                    'waste_type' => $demoCollection['waste_type'],
                ],
                ['amount' => $demoCollection['amount']],
            );
        }
    }
}
