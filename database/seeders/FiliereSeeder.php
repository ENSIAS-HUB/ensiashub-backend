<?php

namespace Database\Seeders;

use App\Models\Filiere;
use Illuminate\Database\Seeder;

class FiliereSeeder extends Seeder
{
    public function run(): void
    {
        $filieres = [
            [
                'id' => '11111111-1111-1111-1111-dddddddddddd',
                'nom' => 'GL',
            ],
            [
                'id' => '22222222-2222-2222-2222-dddddddddddd',
                'nom' => 'D2S',
            ],
            [
                'id' => '33333333-3333-3333-3333-dddddddddddd',
                'nom' => '2SCL',
            ],
            [
                'id' => '44444444-4444-4444-4444-dddddddddddd',
                'nom' => 'SSE',
            ],
            [
                'id' => '55555555-5555-5555-5555-dddddddddddd',
                'nom' => '2AI',
            ],
        ];

        foreach ($filieres as $filiere) {
            Filiere::create($filiere);
        }
    }
}