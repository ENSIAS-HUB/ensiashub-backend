<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('profile_activity')) return;
        Schema::create('profile_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'post_created',
                'comment_added',
                'document_added',
                'club_joined',
                'project_added',
                'post_liked',
            ]);
            $table->nullableMorphs('subject');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_activity');
    }
};
