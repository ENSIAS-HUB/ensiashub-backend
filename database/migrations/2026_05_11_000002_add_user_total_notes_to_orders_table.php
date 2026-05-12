<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->decimal('total', 10, 2)->default(0)->after('tempsAttenteEstime');
            $table->text('notes')->nullable()->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'total', 'notes']);
        });
    }
};
