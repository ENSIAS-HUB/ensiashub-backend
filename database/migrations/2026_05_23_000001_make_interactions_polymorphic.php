<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make publication_id nullable (drop FK, alter column, re-add FK)
        Schema::table('interactions', function (Blueprint $table) {
            $table->dropForeign(['publication_id']);
        });

        // Use raw SQL for nullable change (no doctrine/dbal needed)
        DB::statement('ALTER TABLE interactions ALTER COLUMN publication_id DROP NOT NULL');

        Schema::table('interactions', function (Blueprint $table) {
            $table->foreign('publication_id')
                  ->references('id')->on('publications')
                  ->onDelete('cascade');

            // Add polymorphic columns
            $table->string('reactable_type')->nullable()->after('publication_id');
            $table->uuid('reactable_id')->nullable()->after('reactable_type');
            $table->index(['reactable_type', 'reactable_id'], 'interactions_reactable_index');
        });

        // 2. Backfill polymorphic columns for existing interactions
        DB::table('interactions')
            ->whereNotNull('publication_id')
            ->update([
                'reactable_type' => 'App\\Models\\Publication',
                'reactable_id'   => DB::raw('publication_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->dropIndex('interactions_reactable_index');
            $table->dropColumn(['reactable_type', 'reactable_id']);
        });

        Schema::table('interactions', function (Blueprint $table) {
            $table->dropForeign(['publication_id']);
        });

        DB::statement('ALTER TABLE interactions ALTER COLUMN publication_id SET NOT NULL');

        Schema::table('interactions', function (Blueprint $table) {
            $table->foreign('publication_id')
                  ->references('id')->on('publications')
                  ->onDelete('cascade');
        });
    }
};
