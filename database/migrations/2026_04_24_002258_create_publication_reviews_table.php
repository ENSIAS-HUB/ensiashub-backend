<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('publication_id');
            $table->uuid('moderateur_id');
            $table->enum('statut', ['EnAttente', 'Valide', 'Rejete'])->default('EnAttente');
            $table->datetime('reviewedAt');
            $table->text('motif')->nullable();
            $table->timestamps();
            
            $table->foreign('publication_id')->references('id')->on('publications')->onDelete('cascade');
            $table->foreign('moderateur_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['publication_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_reviews');
    }
};