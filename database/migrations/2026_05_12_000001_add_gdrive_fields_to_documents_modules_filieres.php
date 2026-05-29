<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── documents ─────────────────────────────────────────────
        Schema::table('documents', function (Blueprint $table) {
            // Drop existing FK before modifying the column
            $table->dropForeign(['uploader_id']);

            // Make upload-specific fields nullable so GDrive-synced
            // documents don't require a local uploader / file path
            $table->string('nom')->nullable()->change();
            $table->string('format')->nullable()->change();
            $table->string('urlStockage')->nullable()->change();
            $table->uuid('uploader_id')->nullable()->change();

            // GDrive-specific fields
            $table->string('gdrive_file_id')->nullable()->unique()->after('downloads_count');
            $table->string('mime_type')->nullable()->after('gdrive_file_id');
            $table->text('preview_url')->nullable()->after('mime_type');
            $table->text('download_url')->nullable()->after('preview_url');
            $table->unsignedBigInteger('taille')->nullable()->after('download_url');

            // Re-add FK (now nullable → SET NULL on delete)
            $table->foreign('uploader_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });

        // ── modules ───────────────────────────────────────────────
        Schema::table('modules', function (Blueprint $table) {
            $table->string('filiere_specifique')->nullable()->after('semestre');
        });

        // ── filieres ──────────────────────────────────────────────
        Schema::table('filieres', function (Blueprint $table) {
            $table->string('code')->nullable()->after('nom');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['uploader_id']);
            $table->dropColumn(['gdrive_file_id', 'mime_type', 'preview_url', 'download_url', 'taille']);
            $table->string('nom')->nullable(false)->change();
            $table->string('format')->nullable(false)->change();
            $table->string('urlStockage')->nullable(false)->change();
            $table->uuid('uploader_id')->nullable(false)->change();
            $table->foreign('uploader_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('filiere_specifique');
        });

        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
