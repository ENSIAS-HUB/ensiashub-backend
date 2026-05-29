<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('documents', 'module_pedagogique_id')) {
            Log::info('Migration P3: colonne module_pedagogique_id déjà absente, rien à faire.');
            return;
        }

        // Étape 1 : supprimer les orphelins sans azure_path ni element_module_id (records morts)
        $dead = DB::table('documents')
            ->whereNull('element_module_id')
            ->whereNull('azure_path')
            ->get(['id', 'titre', 'module_pedagogique_id']);

        foreach ($dead as $doc) {
            Log::warning("P3 — suppression document mort : id={$doc->id} titre={$doc->titre}");
        }
        DB::table('documents')
            ->whereNull('element_module_id')
            ->whereNull('azure_path')
            ->delete();

        // Étape 2 : vérifier qu'il ne reste plus d'orphelins
        $remaining = DB::table('documents')->whereNull('element_module_id')->count();

        if ($remaining === 0) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropColumn('module_pedagogique_id');
            });
            Log::info('P3 — colonne module_pedagogique_id supprimée avec succès.');
        } else {
            Log::warning("P3 — {$remaining} docs sans element_module_id subsistent, colonne conservée.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('documents', 'module_pedagogique_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedBigInteger('module_pedagogique_id')->nullable();
            });
        }
    }
};
