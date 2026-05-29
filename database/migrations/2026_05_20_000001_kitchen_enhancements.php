<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend orders.statut enum to include 'Confirme'
        //    In PostgreSQL, enum is stored as a CHECK constraint — drop & recreate it.
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_statut_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_statut_check CHECK (statut IN ('EnAttente', 'Confirme', 'EnPreparation', 'Prete', 'Recuperee', 'Annulee'))");

        // 2. Add special_instructions to order_lines
        Schema::table('order_lines', function (Blueprint $table) {
            $table->text('special_instructions')->nullable()->after('totalLigne');
        });

        // 3. Add preparation_time_minutes to menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('preparation_time_minutes')->default(10)->after('estDisponible');
        });
    }

    public function down(): void
    {
        // Revert orders.statut constraint
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_statut_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_statut_check CHECK (statut IN ('EnAttente', 'EnPreparation', 'Prete', 'Recuperee', 'Annulee'))");

        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('special_instructions');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('preparation_time_minutes');
        });
    }
};
