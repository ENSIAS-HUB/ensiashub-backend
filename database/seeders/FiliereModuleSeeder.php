<?php

namespace Database\Seeders;

use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FiliereModuleSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'GL'   => [
                'desc'    => 'Génie Logiciel',
                'modules' => ['Algorithmique', 'POO', 'Réseaux', 'BDD', 'Génie Logiciel', 'Architecture Logicielle'],
            ],
            'IDF'  => [
                'desc'    => 'Ingénierie des Données et Finances',
                'modules' => ['Finances', 'Management', 'Droit', 'Marketing', 'Audit', 'Comptabilité'],
            ],
            'D2S'  => [
                'desc'    => 'Data Science & IA',
                'modules' => ['Statistiques', 'Machine Learning', 'Big Data', 'Cloud Computing', 'DevOps'],
            ],
            'SSE'  => [
                'desc'    => 'Systèmes & Électronique',
                'modules' => ['Systèmes Embarqués', 'IoT', 'FPGA', 'Électronique', 'Signal & Traitement'],
            ],
            '2SCL' => [
                'desc'    => 'Supply Chain & Logistique',
                'modules' => ['Logistique', 'Supply Chain', 'Gestion des stocks', 'Transport', 'ERP'],
            ],
            '2AI'  => [
                'desc'    => 'Audit & Intelligence d\'affaires',
                'modules' => ['Audit SI', 'Business Intelligence', 'Gouvernance IT', 'Contrôle de gestion'],
            ],
        ];

        foreach ($data as $nom => $info) {
            $slug = Str::slug($nom);

            $filiere = Filiere::firstOrCreate(
                ['nom' => $nom],
                [
                    'slug'        => $slug,
                    'description' => $info['desc'],
                    'is_active'   => true,
                ]
            );

            // Backfill slug on existing records
            if (! $filiere->slug) {
                $filiere->update(['slug' => $slug, 'is_active' => true]);
            }

            foreach ($info['modules'] as $moduleName) {
                $moduleSlug = Str::slug($moduleName);

                $module = Module::firstOrCreate(
                    [
                        'filiere_id' => $filiere->id,
                        'nom'        => $moduleName,
                    ],
                    [
                        'slug'      => $moduleSlug,
                        'semestre'  => 'S1',
                        'annee'     => 1,
                        'is_active' => true,
                    ]
                );

                if (! $module->slug) {
                    $module->update(['slug' => $moduleSlug, 'is_active' => true]);
                }
            }
        }
    }
}
