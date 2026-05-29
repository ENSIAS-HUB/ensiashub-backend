<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── documents: Azure & Drive columns ──────────────────────────────────
        Schema::table('documents', function (Blueprint $table) {
            // Azure Blob Storage path (ex: drive/gl/S2/algo/2024/uuid.pdf)
            $table->string('azure_path')->nullable()->after('download_url');
            // Public Azure URL
            $table->text('azure_url')->nullable()->after('azure_path');
            // File extension (pdf, docx, pptx…)
            $table->string('extension', 20)->nullable()->after('azure_url');
            // Direct filiere FK (optional — allows filiere-level docs without a module)
            $table->uuid('filiere_id')->nullable()->after('module_pedagogique_id');
            // View counter
            $table->unsignedInteger('views_count')->default(0)->after('downloads_count');
            // Drive-level semester & year (can differ from module.semestre)
            $table->string('semester', 5)->nullable()->after('views_count');
            $table->unsignedSmallInteger('year')->nullable()->after('semester');

            $table->foreign('filiere_id')
                  ->references('id')->on('filieres')
                  ->onDelete('set null');
        });

        // ── filieres: slug, description, is_active ────────────────────────────
        Schema::table('filieres', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('nom');
            $table->string('description')->nullable()->after('slug');
            $table->boolean('is_active')->default(true)->after('description');
        });

        // ── modules: slug, is_active ──────────────────────────────────────────
        Schema::table('modules', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nom');
            $table->boolean('is_active')->default(true)->after('annee');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['filiere_id']);
            $table->dropColumn(['azure_path', 'azure_url', 'extension', 'filiere_id', 'views_count', 'semester', 'year']);
        });

        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'is_active']);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['slug', 'is_active']);
        });
    }
};
