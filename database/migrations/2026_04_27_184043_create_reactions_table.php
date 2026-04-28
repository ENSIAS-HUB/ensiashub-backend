<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reaction'); // 'like', 'love', 'laugh', 'sad', 'angry'
            $table->timestamps();
            
            $table->foreign('id')->references('id')->on('interactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};