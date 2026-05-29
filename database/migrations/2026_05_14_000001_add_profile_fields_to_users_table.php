<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = Schema::getColumnListing('users');

            if (!in_array('username', $columns))     $table->string('username')->unique()->nullable()->after('annee');
            if (!in_array('avatar_url', $columns))   $table->string('avatar_url')->nullable()->after('username');
            if (!in_array('cover_url', $columns))    $table->string('cover_url')->nullable()->after('avatar_url');
            if (!in_array('ville', $columns))        $table->string('ville')->nullable()->after('cover_url');
            if (!in_array('linkedin_url', $columns)) $table->string('linkedin_url')->nullable()->after('ville');
            if (!in_array('github_url', $columns))   $table->string('github_url')->nullable()->after('linkedin_url');
            if (!in_array('website_url', $columns))  $table->string('website_url')->nullable()->after('github_url');
            if (!in_array('phone', $columns))        $table->string('phone')->nullable()->after('website_url');
            if (!in_array('competences', $columns))  $table->json('competences')->nullable()->after('phone');
            if (!in_array('specialite', $columns))   $table->string('specialite')->nullable()->after('competences');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'avatar_url', 'cover_url', 'ville',
                'linkedin_url', 'github_url', 'website_url', 'phone',
                'competences', 'specialite',
            ]);
        });
    }
};
