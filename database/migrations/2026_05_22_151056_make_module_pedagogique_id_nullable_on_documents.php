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
        Schema::table('documents', function (Blueprint $table) {
            // Rendre nullable pour permettre les imports Azure
            // (qui utilisent element_module_id à la place)
            $table->dropForeign(['module_pedagogique_id']);
            $table->uuid('module_pedagogique_id')->nullable()->change();
            $table->foreign('module_pedagogique_id')
                  ->references('id')->on('modules')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['module_pedagogique_id']);
            $table->uuid('module_pedagogique_id')->nullable(false)->change();
            $table->foreign('module_pedagogique_id')
                  ->references('id')->on('modules')
                  ->onDelete('cascade');
        });
    }
};
