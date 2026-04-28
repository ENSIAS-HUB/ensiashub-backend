<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Utilisation de UUID comme clé primaire
            $table->string('emailInstitutionnel')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('password'); // Nécessaire pour authentifier()
            $table->string('photoProfil')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('profileActif')->default(true);
            $table->json('roles'); // Stockage des rôles (ex: ["student", "admin"])
            $table->rememberToken();
            $table->timestamps(); // Gère automatiquement createdAt et updatedAt
        });

        // Tables par défaut de Laravel pour la gestion des sessions et mots de passe
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
