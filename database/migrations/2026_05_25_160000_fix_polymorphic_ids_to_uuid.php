<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les colonnes *_id des tables polymorphiques étaient VARCHAR(36).
     * PostgreSQL refuse la comparaison implicite uuid = character varying.
     * On les passe en type uuid natif pour aligner avec les PK des modèles.
     */
    public function up(): void
    {
        // saved_items — drop unique constraint + index before altering column type
        DB::statement('ALTER TABLE saved_items DROP CONSTRAINT IF EXISTS saved_items_user_id_saveable_type_saveable_id_unique');
        DB::statement('DROP INDEX IF EXISTS saved_items_saveable_type_saveable_id_index');
        DB::statement('ALTER TABLE saved_items ALTER COLUMN saveable_id TYPE uuid USING saveable_id::uuid');
        DB::statement('ALTER TABLE saved_items ADD CONSTRAINT saved_items_user_id_saveable_type_saveable_id_unique UNIQUE (user_id, saveable_type, saveable_id)');

        // comments
        DB::statement('ALTER TABLE comments ALTER COLUMN commentable_id TYPE uuid USING commentable_id::uuid');

        // shares
        DB::statement('ALTER TABLE shares ALTER COLUMN shareable_id TYPE uuid USING shareable_id::uuid');

        // reports
        DB::statement('ALTER TABLE reports ALTER COLUMN reportable_id TYPE uuid USING reportable_id::uuid');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE saved_items DROP CONSTRAINT IF EXISTS saved_items_user_id_saveable_type_saveable_id_unique');
        DB::statement('ALTER TABLE saved_items ALTER COLUMN saveable_id TYPE VARCHAR(36) USING saveable_id::text');
        DB::statement('ALTER TABLE saved_items ADD CONSTRAINT saved_items_user_id_saveable_type_saveable_id_unique UNIQUE (user_id, saveable_type, saveable_id)');

        DB::statement('ALTER TABLE comments ALTER COLUMN commentable_id TYPE VARCHAR(36) USING commentable_id::text');
        DB::statement('ALTER TABLE shares ALTER COLUMN shareable_id TYPE VARCHAR(36) USING shareable_id::text');
        DB::statement('ALTER TABLE reports ALTER COLUMN reportable_id TYPE VARCHAR(36) USING reportable_id::text');
    }
};
