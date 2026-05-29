<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tous les modèles "saveables/reportables/shareables" utilisent des UUID.
     * La colonne *_id était unsignedBigInteger → PostgreSQL rejette les UUIDs.
     * Toutes ces tables sont vides → ALTER TYPE sans perte de données.
     *
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL: ALTER COLUMN using USING cast
        // saved_items
        DB::statement('ALTER TABLE saved_items ALTER COLUMN saveable_id TYPE VARCHAR(36) USING saveable_id::varchar');
        DB::statement('ALTER TABLE saved_items DROP CONSTRAINT IF EXISTS saved_items_user_id_saveable_type_saveable_id_unique');
        DB::statement('ALTER TABLE saved_items ADD CONSTRAINT saved_items_user_id_saveable_type_saveable_id_unique UNIQUE (user_id, saveable_type, saveable_id)');

        // reports
        DB::statement('ALTER TABLE reports ALTER COLUMN reportable_id TYPE VARCHAR(36) USING reportable_id::varchar');

        // shares
        DB::statement('ALTER TABLE shares ALTER COLUMN shareable_id TYPE VARCHAR(36) USING shareable_id::varchar');

        // comments (commentable_id)
        DB::statement('ALTER TABLE comments ALTER COLUMN commentable_id TYPE VARCHAR(36) USING commentable_id::varchar');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE saved_items ALTER COLUMN saveable_id TYPE BIGINT USING saveable_id::bigint');
        DB::statement('ALTER TABLE reports ALTER COLUMN reportable_id TYPE BIGINT USING reportable_id::bigint');
        DB::statement('ALTER TABLE shares ALTER COLUMN shareable_id TYPE BIGINT USING shareable_id::bigint');
        DB::statement('ALTER TABLE comments ALTER COLUMN commentable_id TYPE BIGINT USING commentable_id::bigint');
    }
};
