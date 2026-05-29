<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Table annees ────────────────────────────────────────────────
        Schema::create('annees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label', 5);   // 1A, 2A, 3A
            $table->unsignedTinyInteger('niveau'); // 1, 2, 3
            $table->timestamps();

            $table->unique('label');
        });

        // ── 2. Colonnes supplémentaires sur filieres ───────────────────────
        Schema::table('filieres', function (Blueprint $table) {
            if (! Schema::hasColumn('filieres', 'badge')) {
                $table->string('badge', 5)->nullable()->after('nom');
            }
            if (! Schema::hasColumn('filieres', 'is_tronc_commun')) {
                $table->boolean('is_tronc_commun')->default(false)->after('is_active');
            }
        });

        // ── 3. annee_id sur modules ────────────────────────────────────────
        Schema::table('modules', function (Blueprint $table) {
            if (! Schema::hasColumn('modules', 'annee_id')) {
                $table->uuid('annee_id')->nullable()->after('filiere_id');
                $table->foreign('annee_id')
                      ->references('id')->on('annees')
                      ->nullOnDelete();
            }
        });

        // ── 4. Table element_modules ───────────────────────────────────────
        Schema::create('element_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->foreign('module_id')
                  ->references('id')->on('modules')
                  ->cascadeOnDelete();
            $table->string('nom');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
        });

        // ── 5. element_module_id sur documents ─────────────────────────────
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'element_module_id')) {
                $table->uuid('element_module_id')->nullable()->after('id');
                $table->foreign('element_module_id')
                      ->references('id')->on('element_modules')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ElementModule::class, 'element_module_id');
            $table->dropColumn('element_module_id');
        });

        Schema::dropIfExists('element_modules');

        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['annee_id']);
            $table->dropColumn('annee_id');
        });

        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn(['badge', 'is_tronc_commun']);
        });

        Schema::dropIfExists('annees');
    }
};
