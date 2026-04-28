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
        Schema::create('order_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->foreign()->references('id')->on('orders')->onDelete('cascade');
            $table->uuid('menu_item_id')->foreign()->references('id')->on('menu_items')->onDelete('cascade');
            $table->integer('quantite');
            $table->float('prixUnitaire');
            $table->float('totalLigne');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
