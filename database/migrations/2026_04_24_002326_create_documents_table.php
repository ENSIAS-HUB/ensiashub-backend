<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('titre');
            $table->string('nom');
            $table->string('format');
            $table->string('urlStockage');
            $table->enum('typeDocument', ['Cours', 'TD', 'Examen', 'Autre']);
            $table->enum('statutValidation', ['EnAttente', 'Valide', 'Rejete'])->default('EnAttente');
            $table->uuid('uploader_id');
            $table->uuid('module_pedagogique_id');
            $table->datetime('publishedAt')->nullable();
            $table->integer('downloads_count')->default(0);
            $table->timestamps();
            
            $table->foreign('uploader_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('module_pedagogique_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};