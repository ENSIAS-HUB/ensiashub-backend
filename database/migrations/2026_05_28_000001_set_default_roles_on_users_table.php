<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure existing NULL roles are backfilled
        DB::table('users')->whereNull('roles')->update([
            'roles' => json_encode(['etudiant']),
        ]);

        // Set DB-level default so future inserts without `roles` get a sane value
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN roles SET DEFAULT '[\"etudiant\"]'::json");
            DB::statement("ALTER TABLE users ALTER COLUMN roles SET NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN roles DROP DEFAULT");
            DB::statement("ALTER TABLE users ALTER COLUMN roles DROP NOT NULL");
        }
    }
};
