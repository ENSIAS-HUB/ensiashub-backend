<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('filiere_id');
            $table->string('nom');
            $table->string('semestre'); // S1, S2, S3, S4, S5, S6
            $table->integer('annee'); // 1, 2, 3
            $table->timestamps();
            
            $table->foreign('filiere_id')->references('id')->on('filieres')->onDelete('cascade');
            
            $table->unique(['filiere_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};