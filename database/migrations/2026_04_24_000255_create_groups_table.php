<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->enum('categorie', ['Filiere', 'Club', 'General']);
            $table->text('description');
            $table->uuid('createur_id');
            $table->datetime('creeLe')->nullable();
            $table->timestamps();
            
            $table->foreign('createur_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};