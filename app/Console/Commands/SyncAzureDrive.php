<?php

namespace App\Console\Commands;

use App\Models\{Document, ElementModule, Module, Filiere, Annee};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncAzureDrive extends Command
{
    protected $signature = 'drive:sync-azure
                            {--prefix=Ensias files/Ensias Drive : Préfixe du dossier Azure}
                            {--filiere= : Slug de la filière cible (ex: tronc-commun, gl, d2s). Obligatoire.}
                            {--dry-run : Simuler sans écrire en DB}
                            {--force : Réimporter même les fichiers déjà présents}';

    protected $description = 'Synchronise les fichiers Azure Blob → table documents';

    /** Filière résolue depuis --filiere et utilisée pour tous les fichiers */
    private Filiere $filiere;

    // Extensions reconnues
    private const ALLOWED_EXT = [
        'pdf', 'doc', 'docx', 'ppt', 'pptx',
        'xls', 'xlsx', 'zip', 'txt', 'png', 'jpg',
    ];

    // Déduire le typeDocument depuis le nom du dossier
    // Valeurs enum valides : Cours, TD/TP, Anciens examens, Résumé, Projet, Autre
    private const TYPE_MAP = [
        'cours'           => 'Cours',
        'course'          => 'Cours',
        'resume'          => 'Cours',
        'resumes'         => 'Cours',
        'correction'      => 'Cours',
        'td'              => 'TD/TP',
        'tp'              => 'TD/TP',
        'tds'             => 'TD/TP',
        'tps'             => 'TD/TP',
        'td-tp'           => 'TD/TP',
        'td_tp'           => 'TD/TP',
        'td et tp'        => 'TD/TP',
        'exercice'        => 'TD/TP',
        'exercices'       => 'TD/TP',
        'enonce'          => 'TD/TP',
        'enoncer'         => 'TD/TP',
        'solution'        => 'TD/TP',
        'examen'          => 'Anciens examens',
        'exam'            => 'Anciens examens',
        'examens'         => 'Anciens examens',
        'exams'           => 'Anciens examens',
        'corrige'         => 'Anciens examens',
        'corriges'        => 'Anciens examens',
        'ancien'          => 'Anciens examens',
        'anciens'         => 'Anciens examens',
        'projet'          => 'Projet',
        'projets'         => 'Projet',
    ];

    public function handle(): int
    {
        $prefix      = $this->option('prefix');
        $dryRun      = $this->option('dry-run');
        $force       = $this->option('force');
        $filiereSlug = $this->option('filiere');

        // ── Résoudre la filière (obligatoire) ────────────────────────────────
        if (! $filiereSlug) {
            $this->error('Spécifier --filiere=slug (ex: --filiere=tronc-commun)');
            $this->info('Filières disponibles :');
            Filiere::all()->each(fn ($f) => $this->line("  --filiere={$f->slug}  ({$f->nom})"));
            return self::FAILURE;
        }

        $filiere = Filiere::where('slug', $filiereSlug)
                          ->orWhere('nom', strtoupper($filiereSlug))
                          ->first();

        if (! $filiere) {
            $this->error("Filière '{$filiereSlug}' introuvable en base.");
            $this->info('Filières disponibles :');
            Filiere::all()->each(fn ($f) => $this->line("  --filiere={$f->slug}  ({$f->nom})"));
            return self::FAILURE;
        }

        $this->filiere = $filiere;
        $this->info("Filière : {$this->filiere->nom} (slug={$this->filiere->slug})");
        $this->info("Listage des fichiers Azure sous : {$prefix}");

        try {
            $allFiles = Storage::disk('azure')->allFiles($prefix);
        } catch (\Exception $e) {
            $this->error('Connexion Azure échouée : ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info(count($allFiles) . ' fichiers trouvés dans Azure.');

        $imported = 0;
        $skipped  = 0;
        $errors   = 0;

        foreach ($allFiles as $azurePath) {
            try {
                $result = $this->processFile($azurePath, $dryRun, $force);
                match ($result) {
                    'imported' => $imported++,
                    'skipped'  => $skipped++,
                    default    => null,
                };
            } catch (\Exception $e) {
                $this->warn("  [ERREUR] {$azurePath} → " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->table(
            ['Importés', 'Ignorés', 'Erreurs'],
            [[$imported, $skipped, $errors]]
        );

        if ($dryRun) {
            $this->warn('Mode dry-run : aucune modification en base.');
        } else {
            $this->info('Synchronisation terminée.');
        }

        return self::SUCCESS;
    }

    private function processFile(string $azurePath, bool $dryRun, bool $force): string
    {
        if (str_starts_with(basename($azurePath), '.')) {
            return 'skipped';
        }

        $ext = strtolower(pathinfo($azurePath, PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_EXT, true)) {
            return 'skipped';
        }

        if (! $force && Document::where('azure_path', $azurePath)->exists()) {
            return 'skipped';
        }

        $parsed = $this->parsePath($azurePath);

        if (! $parsed) {
            $this->warn("  [?] Structure non reconnue : {$azurePath}");
            return 'skipped';
        }

        $this->line("  [OK] {$parsed['annee']}/{$parsed['semestre']} | "
                  . "{$parsed['module']} > {$parsed['element']} > {$parsed['typeDocument']} | "
                  . "{$parsed['filename']}");

        if ($dryRun) {
            return 'imported';
        }

        // Hiérarchie : filière passée en paramètre → année → module → élément
        $annee   = $this->findOrCreateAnnee($parsed['annee']);
        $module  = $this->findOrCreateModule($this->filiere, $annee, $parsed['module'], $parsed['semestre']);
        $element = $this->findOrCreateElement($module, $parsed['element']);

        $taille   = Storage::disk('azure')->size($azurePath);
        $azureUrl = rtrim(
            config('filesystems.disks.azure.url')
            ?? ('https://' . config('filesystems.disks.azure.name') . '.blob.core.windows.net'),
            '/'
        ) . '/' . config('filesystems.disks.azure.container')
            . '/' . ltrim($azurePath, '/');

        Document::updateOrCreate(
            ['azure_path' => $azurePath],
            [
                'element_module_id' => $element->id,
                'filiere_id'        => $this->filiere->id,
                'uploader_id'       => null,
                'titre'             => $this->buildTitle($parsed),
                'nom'               => basename($azurePath),
                'typeDocument'      => $parsed['typeDocument'],
                'statutValidation'  => 'Valide',
                'azure_path'        => $azurePath,
                'azure_url'         => $azureUrl,
                'extension'         => $ext,
                'mime_type'         => $this->guessMime($ext),
                'taille'            => $taille,
                'semester'          => $parsed['semestre'],
                'format'            => strtoupper($ext),
                'urlStockage'       => $azureUrl,
            ]
        );

        return 'imported';
    }

    /**
     * Structure Azure RÉELLE (strict positionnel) :
     *   {prefix}/{annee}/{semestre}/{module}/{element}/{type}/{fichier}
     *
     * Exemples :
     *   1A/S2/Introduction à l_IA/Introduction à l_IA symbolique/Cours/Partie 2.pdf
     *   1A/S1/Algorithmique et structures de données/Algorithmique/TD/solution/[ 1 ].docx
     *   1A/S2/Module de spécialité/D2S/TD/file.pdf
     *
     * La filière n'est PAS dans le chemin — elle est passée via --filiere.
     */
    private function parsePath(string $fullPath): ?array
    {
        $prefix   = $this->option('prefix');
        $relative = ltrim(str_replace($prefix, '', $fullPath), '/');
        $parts    = explode('/', $relative);

        // Minimum : annee/semestre/module/fichier (4 parties)
        if (count($parts) < 4) {
            return null;
        }

        $annee    = $parts[0];   // 1A
        $semestre = $parts[1];   // S1, S2

        // Valider annee
        if (! preg_match('/^\d[Aa]$/', $annee)) {
            return null;
        }

        // Valider semestre
        if (! preg_match('/^[Ss]\d$/', $semestre)) {
            return null;
        }

        $filename = end($parts); // toujours le dernier segment

        // Valider extension du fichier
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_EXT, true)) {
            return null;
        }

        $n = count($parts);

        if ($n === 4) {
            // annee/semestre/module/fichier
            $module   = $parts[2];
            $element  = $parts[2];   // pas d'élément distinct
            $typRaw   = null;
        } elseif ($n === 5) {
            // annee/semestre/module/type_ou_element/fichier
            $module  = $parts[2];
            $typeCandidate = $this->normalizeType($parts[3]);
            if ($typeCandidate !== 'Cours' || strtolower($parts[3]) === 'cours') {
                // parts[3] est un dossier type (TD, Examen…)
                $element = $parts[2];
                $typRaw  = $parts[3];
            } else {
                $element = $parts[3];
                $typRaw  = null;
            }
        } else {
            // n >= 6 : annee/semestre/module/element/type[/subfolder]/fichier
            $module  = $parts[2];
            $element = $parts[3];
            $typRaw  = $parts[4];
            // parts[5..n-2] sont des sous-dossiers ignorés, parts[n-1] = fichier
        }

        return [
            'annee'        => strtoupper($annee),
            'semestre'     => strtoupper($semestre),
            'module'       => trim($module),
            'element'      => trim($element),
            'typeDocument' => $this->normalizeType($typRaw),
            'filename'     => $filename,
        ];
    }

    /** Normalise un libellé de dossier vers l'enum typeDocument */
    private function normalizeType(?string $raw): string
    {
        if (! $raw) {
            return 'Cours';
        }
        $key = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', trim($raw)) ?: trim($raw));
        // Correspondance exacte
        if (isset(self::TYPE_MAP[$key])) {
            return self::TYPE_MAP[$key];
        }
        // Correspondance partielle
        foreach (self::TYPE_MAP as $pattern => $value) {
            if (str_contains($key, $pattern)) {
                return $value;
            }
        }
        return 'Cours';
    }

    // ── Helpers find-or-create ────────────────────────────────────────────────

    private function findOrCreateAnnee(string $label): Annee
    {
        return Annee::firstOrCreate(
            ['label' => strtoupper($label)],
            ['niveau' => (int) $label[0]]
        );
    }

    private function findOrCreateModule(Filiere $filiere, Annee $annee, string $nom, string $semestre = 'S1'): Module
    {
        // Unique constraint in DB is (filiere_id, nom) → use that as lookup key
        return Module::firstOrCreate(
            ['filiere_id' => $filiere->id, 'nom' => $nom],
            [
                'annee_id'  => $annee->id,
                'slug'      => Str::slug($nom),
                'semestre'  => $semestre,
                'is_active' => true,
            ]
        );
    }

    private function findOrCreateElement(Module $module, string $nom): ElementModule
    {
        return ElementModule::firstOrCreate(
            [
                'module_id' => $module->id,
                'slug'      => Str::slug($nom),
            ],
            ['nom' => $nom]
        );
    }

    private function buildTitle(array $parsed): string
    {
        $name = pathinfo($parsed['filename'], PATHINFO_FILENAME);
        $name = str_replace(['_', '-'], ' ', $name);
        return ucwords(strtolower($name));
    }

    private function guessMime(string $ext): string
    {
        return match ($ext) {
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'zip'  => 'application/zip',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'txt'  => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
