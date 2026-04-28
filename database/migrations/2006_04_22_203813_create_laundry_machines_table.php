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
        Schema::create('laundry_machines', function (Blueprint $table) {
           $table->uuid('id')->primary();
           $table->string('numeroMachine')->unique();
           $table->enum('type', ['LaveLinge', 'SecheLinge']);
           $table->enum('status', ['Libre', 'Occupee', 'HorsService']);
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_machines');
    }
};
