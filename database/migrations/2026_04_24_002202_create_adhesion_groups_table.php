<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adhesion_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('group_id');
            $table->enum('statut', ['EnAttente', 'Approuve', 'Rejete', 'Banni']);
            $table->enum('role', ['Membre', 'Moderateur']);
            $table->datetime('joinedAt');
            $table->datetime('reviewedAt')->nullable();
            $table->text('motifDecision')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            
            $table->unique(['user_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adhesion_groups');
    }
};