<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function existingIndexes(string $table): array
    {
        return collect(DB::select(
            "SELECT indexname FROM pg_indexes WHERE tablename = ?",
            [$table]
        ))->pluck('indexname')->toArray();
    }

    public function up(): void
    {
        // ── documents ────────────────────────────────────────────
        Schema::table('documents', function (Blueprint $table) {
            $idx = $this->existingIndexes('documents');

            if (!in_array('documents_element_module_id_index', $idx)) {
                $table->index('element_module_id');
            }
            if (!in_array('documents_azure_path_index', $idx)) {
                $table->index('azure_path');
            }
            if (!in_array('documents_type_document_index', $idx)) {
                $table->index('typeDocument', 'documents_type_document_index');
            }
            if (!in_array('documents_uploader_id_index', $idx)) {
                $table->index('uploader_id', 'documents_uploader_id_index');
            }
            if (!in_array('documents_element_type_index', $idx)) {
                $table->index(['element_module_id', 'typeDocument'], 'documents_element_type_index');
            }
        });

        // ── modules ──────────────────────────────────────────────
        Schema::table('modules', function (Blueprint $table) {
            $idx = $this->existingIndexes('modules');

            if (!in_array('modules_filiere_id_index', $idx)) {
                $table->index('filiere_id');
            }
            if (!in_array('modules_annee_id_index', $idx)) {
                $table->index('annee_id');
            }
            if (!in_array('modules_semestre_index', $idx)) {
                $table->index('semestre');
            }
            if (!in_array('modules_slug_index', $idx)) {
                $table->index('slug');
            }
            if (!in_array('modules_filiere_annee_semestre_index', $idx)) {
                $table->index(['filiere_id', 'annee_id', 'semestre'], 'modules_filiere_annee_semestre_index');
            }
        });

        // ── element_modules ──────────────────────────────────────
        Schema::table('element_modules', function (Blueprint $table) {
            $idx = $this->existingIndexes('element_modules');

            if (!in_array('element_modules_module_id_index', $idx)) {
                $table->index('module_id');
            }
            if (!in_array('element_modules_slug_index', $idx)) {
                $table->index('slug');
            }
        });

        // ── users ────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $idx = $this->existingIndexes('users');

            // Note: 'roles' is a JSON array — not indexable with btree; skipped.
            if (!in_array('users_filiere_index', $idx) && Schema::hasColumn('users', 'filiere')) {
                $table->index('filiere');
            }
            if (!in_array('users_annee_index', $idx) && Schema::hasColumn('users', 'annee')) {
                $table->index('annee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndexIfExists(['element_module_id']);
            $table->dropIndexIfExists(['azure_path']);
            $table->dropIndexIfExists('documents_type_document_index');
            $table->dropIndexIfExists('documents_uploader_id_index');
            $table->dropIndexIfExists('documents_element_type_index');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndexIfExists(['filiere_id']);
            $table->dropIndexIfExists(['annee_id']);
            $table->dropIndexIfExists(['semestre']);
            $table->dropIndexIfExists(['slug']);
            $table->dropIndexIfExists('modules_filiere_annee_semestre_index');
        });

        Schema::table('element_modules', function (Blueprint $table) {
            $table->dropIndexIfExists(['module_id']);
            $table->dropIndexIfExists(['slug']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists(['filiere']);
            $table->dropIndexIfExists(['annee']);
        });
    }
};
