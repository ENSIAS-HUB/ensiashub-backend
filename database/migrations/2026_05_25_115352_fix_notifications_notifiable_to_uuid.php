<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remplace notifiable_id (bigint) par uuid dans la table notifications.
     * Nécessaire car le modèle User utilise HasUuids (clé primaire UUID).
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
            $table->uuidMorphs('notifiable');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
            $table->morphs('notifiable');
        });
    }
};
