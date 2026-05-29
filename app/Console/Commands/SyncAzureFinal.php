<?php

namespace App\Console\Commands;

use App\Models\{Document, ElementModule, Module, Filiere, Annee};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncAzureFinal extends Command
{
    protected $signature = 'drive:sync-final
        {--prefix=Ensias files/Ensias Drive : Chemin racine dans Azure}
        {--filiere=tc : Slug de la filière (tc, gl, d2s, sse, idf...)}
        {--annee= : Filtrer sur une année (1A, 2A, 3A). Vide = toutes.}
        {--dry-run : Analyser sans écrire}
        {--force : Réimporter même les fichiers déjà en DB}
        {--reset : ⚠️ Vider documents + element_modules avant sync}';

    protected $description = 'Synchronisation Azure Blob → DB (version finale avec semestre)';

    private const EXT_OK = [
        'pdf', 'doc', 'docx', 'ppt', 'pptx',
        'xls', 'xlsx', 'zip', 'txt', 'png', 'jpg', 'jpeg',
    ];

    // Mapping dossier Azure → typeDocument (enum DB)
    private const TYPE_MAP = [
        'cours'           => 'Cours',
        'course'          => 'Cours',
        'td'              => 'TD/TP',
        'tp'              => 'TD/TP',
        'td-tp'           => 'TD/TP',
        'td_tp'           => 'TD/TP',
        'td et tp'        => 'TD/TP',
        'tds'             => 'TD/TP',
        'tps'             => 'TD/TP',
        'exercice'        => 'TD/TP',
        'exercices'       => 'TD/TP',
        'enonce'          => 'TD/TP',
        'solution'        => 'TD/TP',
        'examen'          => 'Anciens examens',
        'examens'         => 'Anciens examens',
        'exam'            => 'Anciens examens',
        'anciens examens' => 'Anciens examens',
        'ancien examen'   => 'Anciens examens',
        'corrige'         => 'Anciens examens',
        'corriges'        => 'Anciens examens',
        'corrigé'         => 'Anciens examens',
        'corrigés'        => 'Anciens examens',
        'ancien'          => 'Anciens examens',
        'anciens'         => 'Anciens examens',
        'resume'          => 'Résumé',
        'résumé'          => 'Résumé',
        'resumes'         => 'Résumé',
        'projet'          => 'Projet',
        'projets'         => 'Projet',
    ];

    private Filiere $filiere;
    private array $stats = ['imported' => 0, 'skipped' => 0, 'errors' => 0];

    public function handle(): int
    {
        $prefix      = $this->option('prefix');
        $dry         = $this->option('dry-run');
        $force       = $this->option('force');
        $anneeFilter = $this->option('annee');
        $filiereSlug = $this->option('filiere');

        // ── Résoudre la filière ────────────────────────────────────────────
        $filiere = Filiere::where('slug', $filiereSlug)
                          ->orWhere('nom', strtoupper($filiereSlug))
                          ->first();

        if (! $filiere) {
            $filiere = Filiere::create([
                'nom'             => strtoupper($filiereSlug),
                'slug'            => Str::slug($filiereSlug),
                'badge'           => strtoupper(substr($filiereSlug, 0, 3)),
                'is_tronc_commun' => in_array(strtolower($filiereSlug), ['tc', 'tronc-commun', 'tronc'], true),
                'is_active'       => true,
            ]);
            $this->warn("⚡ Filière créée : {$filiere->nom}");
        }

        $this->filiere = $filiere;
        $this->info("📚 Filière   : {$this->filiere->nom}");
        $this->info("📂 Préfixe  : {$prefix}");
        if ($anneeFilter) {
            $this->info("📅 Filtre année : {$anneeFilter}");
        }

        // ── Reset optionnel ────────────────────────────────────────────────
        if ($this->option('reset') && ! $dry) {
            if ($this->confirm('⚠️ Vider documents et element_modules pour cette filière ?')) {
                $moduleIds  = Module::where('filiere_id', $this->filiere->id)->pluck('id');
                $elementIds = ElementModule::whereIn('module_id', $moduleIds)->pluck('id');
                Document::whereIn('element_module_id', $elementIds)->forceDelete();
                ElementModule::whereIn('module_id', $moduleIds)->delete();
                $this->warn('🗑️ Documents et éléments supprimés.');
            }
        }

        // ── Connexion Azure ───────────────────────────────────────────────
        $this->info('🔌 Connexion Azure...');
        try {
            $allFiles = Storage::disk('azure')->allFiles($prefix);
        } catch (\Exception $e) {
            $this->error('❌ Azure KO : ' . $e->getMessage());
            return self::FAILURE;
        }
        $this->info('📁 ' . count($allFiles) . ' fichiers trouvés dans Azure.');

        // ── Traitement ────────────────────────────────────────────────────
        foreach ($allFiles as $azurePath) {
            $basename = basename($azurePath);
            if (str_starts_with($basename, '.') || $basename === '') {
                continue;
            }

            $ext = strtolower(pathinfo($azurePath, PATHINFO_EXTENSION));
            if (! in_array($ext, self::EXT_OK, true)) {
                $this->stats['skipped']++;
                continue;
            }

            $parsed = $this->parsePath($azurePath, $prefix);
            if (! $parsed) {
                $this->warn("  ❓ Non parsé : {$azurePath}");
                $this->stats['skipped']++;
                continue;
            }

            if ($anneeFilter && strtoupper($parsed['annee']) !== strtoupper($anneeFilter)) {
                $this->stats['skipped']++;
                continue;
            }

            if ($dry) {
                $this->line(sprintf(
                    "  📄 %s › %s › %s › %s › %s › %s",
                    $parsed['annee'],
                    $parsed['semestre'],
                    $parsed['module'],
                    $parsed['element'],
                    $parsed['type'],
                    $parsed['fichier']
                ));
                $this->stats['imported']++;
                continue;
            }

            if (! $force && Document::where('azure_path', $azurePath)->exists()) {
                $this->stats['skipped']++;
                continue;
            }

            try {
                $this->importDocument($azurePath, $parsed, $ext);
                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->warn("  ❌ {$azurePath} → " . $e->getMessage());
                $this->stats['errors']++;
            }
        }

        // ── Résumé ────────────────────────────────────────────────────────
        $this->newLine();
        $this->table(
            ['Importés', 'Ignorés', 'Erreurs'],
            [[$this->stats['imported'], $this->stats['skipped'], $this->stats['errors']]]
        );

        if ($dry) {
            $this->warn('🔶 Dry-run : aucune modification en DB.');
        } else {
            $this->info('✅ Sync terminée !');
            $this->info('📊 DB : ' . Document::count() . ' documents total.');
        }

        return self::SUCCESS;
    }

    /**
     * Structure Azure attendue (strictement positionnelle) :
     *   {prefix}/{annee}/{semestre}/{module}/{element}/{type}/{fichier}
     *
     * Exemples réels :
     *   1A/S2/Introduction à l_IA/Introduction à l_IA symbolique/Cours/Partie 2.pdf
     *   1A/S1/Algorithmique et structures de données/Algorithmique/TD/TP1.pdf
     *   2A/S3/Architecture Logicielle/Design Patterns/TD/Exercice1.pdf
     *
     * La filière N'EST PAS dans le chemin → passée via --filiere.
     */
    private function parsePath(string $fullPath, string $prefix): ?array
    {
        $clean  = ltrim(str_replace($prefix, '', $fullPath), '/');
        $parts  = array_values(array_filter(explode('/', $clean), fn ($p) => $p !== ''));

        if (count($parts) < 3) {
            return null;
        }

        // ── Trouver l'ANNÉE ───────────────────────────────────────────────
        $idx   = 0;
        $annee = null;
        foreach ($parts as $i => $p) {
            if (preg_match('/^\d[Aa]$/', trim($p))) {
                $annee = strtoupper(trim($p));
                $idx   = $i + 1;
                break;
            }
        }
        if (! $annee) {
            return null;
        }

        // ── Trouver le SEMESTRE ───────────────────────────────────────────
        $semestre = 'S1';
        if (isset($parts[$idx]) && preg_match('/^[Ss]\d$/', trim($parts[$idx]))) {
            $semestre = strtoupper(trim($parts[$idx]));
            $idx++;
        }

        // ── Parties restantes après annee + semestre ──────────────────────
        $remaining = array_slice($parts, $idx);
        if (count($remaining) < 2) {
            return null;
        }

        $fichier = array_pop($remaining);
        if (! pathinfo($fichier, PATHINFO_EXTENSION)) {
            return null;
        }

        // ── Détecter le TYPE dans les parties restantes ───────────────────
        $type    = 'Cours';
        $typeIdx = null;
        foreach ($remaining as $i => $part) {
            $key = mb_strtolower(trim(iconv('UTF-8', 'ASCII//TRANSLIT', $part) ?: $part));
            if (isset(self::TYPE_MAP[$key])) {
                $type    = self::TYPE_MAP[$key];
                $typeIdx = $i;
                break;
            }
            // correspondance partielle
            foreach (self::TYPE_MAP as $pattern => $value) {
                if (str_contains($key, $pattern)) {
                    $type    = $value;
                    $typeIdx = $i;
                    break 2;
                }
            }
        }
        if ($typeIdx !== null) {
            array_splice($remaining, $typeIdx, 1);
        }

        // ── MODULE et ÉLÉMENT ─────────────────────────────────────────────
        $module  = isset($remaining[0]) ? trim($remaining[0]) : null;
        $element = isset($remaining[1]) ? trim($remaining[1]) : null;

        if (! $module) {
            return null;
        }
        if (! $element) {
            $element = $module; // structure plate → élément = module
        }

        return [
            'annee'    => $annee,
            'semestre' => $semestre,
            'module'   => $module,
            'element'  => $element,
            'type'     => $type,
            'fichier'  => $fichier,
        ];
    }

    private function importDocument(string $azurePath, array $parsed, string $ext): void
    {
        $annee = Annee::firstOrCreate(
            ['label' => $parsed['annee']],
            ['niveau' => (int) $parsed['annee'][0]]
        );

        // Unique constraint on modules is (filiere_id, nom)
        $module = Module::firstOrCreate(
            ['filiere_id' => $this->filiere->id, 'nom' => $parsed['module']],
            [
                'annee_id'  => $annee->id,
                'slug'      => Str::slug($parsed['module']),
                'semestre'  => $parsed['semestre'],
                'is_active' => true,
            ]
        );

        // Mettre à jour le semestre s'il manquait
        if (! $module->semestre) {
            $module->update(['semestre' => $parsed['semestre']]);
        }

        $element = ElementModule::firstOrCreate(
            ['module_id' => $module->id, 'slug' => Str::slug($parsed['element'])],
            ['nom' => $parsed['element']]
        );

        $azureUrl = rtrim(
            config('filesystems.disks.azure.url')
            ?? ('https://' . config('filesystems.disks.azure.name') . '.blob.core.windows.net'),
            '/'
        ) . '/' . config('filesystems.disks.azure.container')
            . '/' . ltrim($azurePath, '/');

        try {
            $size = Storage::disk('azure')->size($azurePath);
        } catch (\Exception) {
            $size = 0;
        }

        $titre = ucwords(strtolower(str_replace(['_', '-'], ' ', pathinfo($parsed['fichier'], PATHINFO_FILENAME))));

        Document::updateOrCreate(
            ['azure_path' => $azurePath],
            [
                'element_module_id' => $element->id,
                'filiere_id'        => $this->filiere->id,
                'uploader_id'       => null,
                'titre'             => $titre,
                'nom'               => $parsed['fichier'],
                'typeDocument'      => $parsed['type'],
                'statutValidation'  => 'Valide',
                'azure_path'        => $azurePath,
                'azure_url'         => $azureUrl,
                'extension'         => $ext,
                'mime_type'         => $this->guessMime($ext),
                'taille'            => $size,
                'semester'          => $parsed['semestre'],
                'format'            => strtoupper($ext),
                'urlStockage'       => $azureUrl,
            ]
        );

        $this->line(sprintf(
            "  ✅ [%s] %s › %s › %s › %s",
            $parsed['semestre'],
            $parsed['module'],
            $parsed['element'],
            $parsed['type'],
            $parsed['fichier']
        ));
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
            'jpg',
            'jpeg' => 'image/jpeg',
            'txt'  => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
