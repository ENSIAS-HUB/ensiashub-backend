<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Determine the publications table name (could be 'posts' or 'publications')
        $table = Schema::hasTable('publications') ? 'publications' : 'posts';

        Schema::table($table, function (Blueprint $blueprint) {
            if (!Schema::hasColumn($blueprint->getTable(), 'source')) {
                $blueprint->string('source')->default('manual')->after('visibility');
            }
            if (!Schema::hasColumn($blueprint->getTable(), 'instagram_url')) {
                $blueprint->string('instagram_url')->nullable()->after('source');
            }
            if (!Schema::hasColumn($blueprint->getTable(), 'imported_at')) {
                $blueprint->timestamp('imported_at')->nullable()->after('instagram_url');
            }
        });
    }

    public function down(): void
    {
        $table = Schema::hasTable('publications') ? 'publications' : 'posts';

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn(['source', 'instagram_url', 'imported_at']);
        });
    }
};
