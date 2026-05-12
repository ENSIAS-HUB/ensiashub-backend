<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Filière de l'étudiant : GL, GD, D2S, 2IA, 2SCL, SSE, CSCMC, IDF, BI&A
            $table->string('filiere')->nullable()->after('roles');
            // Année : 1A, 2A, 3A
            $table->string('annee')->nullable()->after('filiere');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['filiere', 'annee']);
        });
    }
};
