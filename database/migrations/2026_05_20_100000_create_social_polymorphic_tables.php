<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Commentaires polymorphiques ────────────────────────────────────
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Polymorphisme
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');

            // Réponses imbriquées
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('comments')
                  ->cascadeOnDelete();

            $table->text('content');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_type', 'commentable_id', 'created_at']);
        });

        // ── Items sauvegardés polymorphiques ──────────────────────────────
        Schema::create('saved_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            $table->string('saveable_type');
            $table->unsignedBigInteger('saveable_id');

            $table->string('collection')->default('default');
            $table->timestamps();

            $table->unique(['user_id', 'saveable_type', 'saveable_id']);
            $table->index(['saveable_type', 'saveable_id']);
        });

        // ── Partages polymorphiques ────────────────────────────────────────
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            $table->string('shareable_type');
            $table->unsignedBigInteger('shareable_id');

            $table->enum('channel', ['internal', 'link', 'whatsapp', 'other'])
                  ->default('link');

            $table->foreignUuid('target_group_id')
                  ->nullable()
                  ->constrained('groups')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['shareable_type', 'shareable_id']);
        });

        // ── Signalements polymorphiques ───────────────────────────────────
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            $table->string('reportable_type');
            $table->unsignedBigInteger('reportable_id');

            $table->enum('reason', [
                'spam',
                'inappropriate',
                'harassment',
                'misinformation',
                'other',
            ]);
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'dismissed'])
                  ->default('pending');
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('shares');
        Schema::dropIfExists('saved_items');
        Schema::dropIfExists('comments');
    }
};
