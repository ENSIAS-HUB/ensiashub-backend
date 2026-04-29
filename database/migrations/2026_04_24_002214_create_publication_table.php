<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('contenu');
            $table->string('typeMedia')->nullable();
            $table->enum('statutValidation', ['EnAttente', 'Valide', 'Rejete'])->default('EnAttente');
            $table->uuid('user_id');
            $table->uuid('groupe_id')->nullable();
            $table->datetime('publishedAt')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('groupe_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};