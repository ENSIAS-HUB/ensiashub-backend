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
        Schema::create('device_events', function (Blueprint $table) {
           $table->uuid('id')->primary();
           $table->foreignUuid('iot_device_id')->constrained()->cascadeOnDelete();
           $table->timestamp('timestamp');
            $table->string('valeurBrute');
            $table->string('statutDerive');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_events');
    }
};
