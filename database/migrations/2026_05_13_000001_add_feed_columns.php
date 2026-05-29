<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── groups: add slug + filiere metadata ───────────────────────────
        Schema::table('groups', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('nom');
            $table->string('filiere_key')->nullable()->after('categorie');   // ex: "IDF", "GL"
            $table->string('annee_filiere')->nullable()->after('filiere_key'); // ex: "1A", "2A"
        });

        // ── adhesion_groups: add auto_assigned flag ───────────────────────
        Schema::table('adhesion_groups', function (Blueprint $table) {
            $table->boolean('auto_assigned')->default(false)->after('motifDecision');
        });

        // ── publications: add visibility + make contenu nullable ──────────
        Schema::table('publications', function (Blueprint $table) {
            $table->enum('visibility', ['global', 'group'])->default('global')->after('statutValidation');
            $table->text('contenu')->nullable()->change();
        });

        // ── post_media: new table ─────────────────────────────────────────
        Schema::create('post_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('publication_id');
            $table->text('url');
            $table->enum('type', ['image', 'video'])->default('image');
            $table->text('thumbnail_url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');

        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });

        Schema::table('adhesion_groups', function (Blueprint $table) {
            $table->dropColumn('auto_assigned');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['slug', 'filiere_key', 'annee_filiere']);
        });
    }
};
