<?php

namespace Database\Seeders;

use App\Models\Annee;
use App\Models\ElementModule;
use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DriveSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Années ──────────────────────────────────────────────────────
        $anneesData = [
            ['label' => '1A', 'niveau' => 1],
            ['label' => '2A', 'niveau' => 2],
            ['label' => '3A', 'niveau' => 3],
        ];

        foreach ($anneesData as $a) {
            Annee::firstOrCreate(['label' => $a['label']], $a);
        }

        $a1 = Annee::where('label', '1A')->first();
        $a2 = Annee::where('label', '2A')->first();
        $a3 = Annee::where('label', '3A')->first();

        // ── 2. Renommer anciens noms incorrects ────────────────────────────
        Filiere::where('nom', '2AI')->update(['nom' => '2IA', 'slug' => '2ia', 'badge' => '2IA']);
        Filiere::where('nom', '2SCL')->update(['nom' => '2CSL', 'slug' => '2csl', 'badge' => '2CSL']);

        // ── 3. Upsert badge + slug sur toutes les filières officielles ─────
        //   GL, GD, 2IA, D2S, 2CSL, BI&A, SSE, CSCMC, IDF
        $filieresDef = [
            'GL'          => ['badge' => 'GL',    'slug' => 'gl'],
            'GD'          => ['badge' => 'GD',    'slug' => 'gd'],
            '2IA'         => ['badge' => '2IA',   'slug' => '2ia'],
            'D2S'         => ['badge' => 'D2S',   'slug' => 'd2s'],
            '2CSL'        => ['badge' => '2CSL',  'slug' => '2csl'],
            'BI&A'        => ['badge' => 'BI&A',  'slug' => 'bi-a'],
            'SSE'         => ['badge' => 'SSE',   'slug' => 'sse'],
            'CSCMC'       => ['badge' => 'CSCMC', 'slug' => 'cscmc'],
            'IDF'         => ['badge' => 'IDF',   'slug' => 'idf'],
        ];

        foreach ($filieresDef as $nom => $data) {
            Filiere::updateOrCreate(
                ['nom' => $nom],
                [
                    'badge'           => $data['badge'],
                    'slug'            => $data['slug'],
                    'is_tronc_commun' => false,
                    'is_active'       => true,
                ]
            );
        }

        // Tronc Commun
        $tc = Filiere::updateOrCreate(
            ['nom' => 'Tronc Commun'],
            [
                'slug'            => 'tronc-commun',
                'badge'           => 'TC',
                'is_tronc_commun' => true,
                'is_active'       => true,
            ]
        );

        // ── 3. Modules Tronc Commun 1A ─────────────────────────────────────
        $modulesTC1 = [
            'Mathématiques'                     => ['math'],
            'Algorithmique & Structures de données' => ['general'],
            'Programmation Orientée Objet'      => ['general'],
            'Systèmes d\'Exploitation'          => ['general'],
            'Réseaux'                           => ['general'],
            'Bases de Données'                  => ['general'],
            'Anglais Technique'                 => ['general'],
            'Gestion de Projet'                 => ['general'],
        ];
        $this->seedModules($tc, $a1, $modulesTC1);

        // ── 4. Modules GL ──────────────────────────────────────────────────
        $gl = Filiere::where('nom', 'GL')->first();
        if ($gl) {
            $modulesGL1 = [
                'Algorithmique Avancée'   => ['general'],
                'Programmation Web 1'     => ['general'],
                'Bases de Données 2'      => ['general'],
            ];
            $this->seedModules($gl, $a1, $modulesGL1);

            $modulesGL2 = [
                'Architecture Logicielle' => ['design-patterns', 'microservices'],
                'Génie Logiciel'          => ['uml', 'tests'],
                'Développement Web'       => ['frontend', 'backend'],
                'Sécurité Informatique'   => ['general'],
                'Cloud Computing'         => ['general'],
            ];
            $this->seedModules($gl, $a2, $modulesGL2);

            $modulesGL3 = [
                'DevOps & CI/CD'          => ['general'],
                'Big Data'                => ['general'],
                'Intelligence Artificielle' => ['machine-learning', 'deep-learning'],
            ];
            $this->seedModules($gl, $a3, $modulesGL3);
        }

        // ── 5. Modules D2S ─────────────────────────────────────────────────
        $d2s = Filiere::where('nom', 'D2S')->first();
        if ($d2s) {
            $this->seedModules($d2s, $a2, [
                'Statistiques Appliquées' => ['general'],
                'Machine Learning'        => ['general'],
                'Big Data'               => ['hadoop', 'spark'],
            ]);
            $this->seedModules($d2s, $a3, [
                'Deep Learning'          => ['general'],
                'NLP'                    => ['general'],
                'Visualisation des données' => ['general'],
            ]);
        }

        // ── 6. Modules SSE ─────────────────────────────────────────────────
        $sse = Filiere::where('nom', 'SSE')->first();
        if ($sse) {
            $this->seedModules($sse, $a2, [
                'Systèmes Embarqués'     => ['general'],
                'IoT'                    => ['general'],
                'Électronique Numérique' => ['general'],
            ]);
        }

        // ── 7. Modules GD ──────────────────────────────────────────────────
        $gd = Filiere::where('nom', 'GD')->first();
        if ($gd) {
            $this->seedModules($gd, $a2, [
                'Design Graphique'       => ['general'],
                'UX/UI Design'           => ['general'],
                'Infographie 3D'         => ['general'],
            ]);
            $this->seedModules($gd, $a3, [
                'Motion Design'          => ['general'],
                'Projet de Fin d\'Études' => ['general'],
            ]);
        }

        // ── 8. Modules BI&A ────────────────────────────────────────────────
        $bia = Filiere::where('nom', 'BI&A')->first();
        if ($bia) {
            $this->seedModules($bia, $a2, [
                'Business Intelligence'  => ['general'],
                'Entrepôts de Données'   => ['general'],
                'Analyse Décisionnelle'  => ['general'],
            ]);
            $this->seedModules($bia, $a3, [
                'Data Mining'            => ['general'],
                'Reporting & Dashboards' => ['general'],
                'Big Data Analytics'     => ['hadoop', 'spark'],
            ]);
        }

        // ── 9. Modules CSCMC ───────────────────────────────────────────────
        $cscmc = Filiere::where('nom', 'CSCMC')->first();
        if ($cscmc) {
            $this->seedModules($cscmc, $a2, [
                'Cybersécurité'          => ['general'],
                'Cryptographie'          => ['general'],
                'Sécurité Réseaux'       => ['general'],
            ]);
            $this->seedModules($cscmc, $a3, [
                'Forensique Numérique'   => ['general'],
                'Sécurité des SI'        => ['general'],
                'Audit & Conformité'     => ['general'],
            ]);
        }

        // ── 10. Modules 2IA (ex-2AI) ───────────────────────────────────────
        $ia = Filiere::where('nom', '2IA')->first();
        if ($ia) {
            $this->seedModules($ia, $a2, [
                'Machine Learning'       => ['general'],
                'Deep Learning'          => ['general'],
                'Vision par Ordinateur'  => ['general'],
            ]);
            $this->seedModules($ia, $a3, [
                'NLP'                    => ['general'],
                'IA Embarquée'           => ['general'],
                'Projet IA'              => ['general'],
            ]);
        }

        // ── 11. Modules 2CSL (ex-2SCL) ─────────────────────────────────────
        $csl = Filiere::where('nom', '2CSL')->first();
        if ($csl) {
            $this->seedModules($csl, $a2, [
                'Sécurité Logicielle'    => ['general'],
                'Développement Sécurisé' => ['general'],
                'Tests & Audit Code'     => ['general'],
            ]);
        }

        // ── 12. Mettre à jour annee_id sur les modules existants ───────────
        // Modules créés par FiliereModuleSeeder ont annee=1 → annee_id 1A
        Module::whereNull('annee_id')->where('annee', 1)->update(['annee_id' => $a1->id]);
        Module::whereNull('annee_id')->where('annee', 2)->update(['annee_id' => $a2->id]);
        Module::whereNull('annee_id')->where('annee', 3)->update(['annee_id' => $a3->id]);
        // Default fallback pour ceux sans annee
        Module::whereNull('annee_id')->whereNull('annee')->update(['annee_id' => $a1->id]);
    }

    private function seedModules(Filiere $filiere, Annee $annee, array $modules): void
    {
        foreach ($modules as $nom => $elementSlugs) {
            $slug = Str::slug($nom);

            // Chercher d'abord par (filiere_id, nom) — contrainte unique existante
            $module = Module::where('filiere_id', $filiere->id)
                            ->where('nom', $nom)
                            ->first();

            if ($module) {
                // Mettre à jour annee_id et slug si absent
                $updates = [];
                if (! $module->annee_id) $updates['annee_id'] = $annee->id;
                if (! $module->slug)     $updates['slug']     = $slug;
                if (! empty($updates))   $module->update($updates);
            } else {
                $module = Module::create([
                    'filiere_id' => $filiere->id,
                    'annee_id'   => $annee->id,
                    'nom'        => $nom,
                    'slug'       => $slug,
                    'semestre'   => 'S1',
                    'annee'      => $annee->niveau,
                    'is_active'  => true,
                ]);
            }

            // Éléments de module
            foreach ($elementSlugs as $elSlug) {
                $elNom = match ($elSlug) {
                    'general'         => 'Général',
                    'design-patterns' => 'Design Patterns',
                    'microservices'   => 'Microservices',
                    'uml'             => 'UML',
                    'tests'           => 'Tests & Qualité',
                    'frontend'        => 'Frontend',
                    'backend'         => 'Backend',
                    'hadoop'          => 'Hadoop',
                    'spark'           => 'Spark',
                    'machine-learning'=> 'Machine Learning',
                    'deep-learning'   => 'Deep Learning',
                    'math'            => 'Mathématiques',
                    default           => ucfirst($elSlug),
                };

                ElementModule::firstOrCreate(
                    ['module_id' => $module->id, 'slug' => $elSlug],
                    ['nom' => $elNom]
                );
            }
        }
    }
}
