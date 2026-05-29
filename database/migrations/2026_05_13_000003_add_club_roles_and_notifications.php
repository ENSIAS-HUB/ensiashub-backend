<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── adhesion_groups: track who reviewed the request ───────────────
        Schema::table('adhesion_groups', function (Blueprint $table) {
            $table->uuid('reviewed_by')->nullable()->after('reviewedAt');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── user_roles: contextual roles (president of club X, delegué of Y) ─
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            // role: same values as User::$roles array (president_club, delegue, etc.)
            $table->string('role');
            // context: 'group' or 'filiere', null for global roles
            $table->string('context_type')->nullable();
            // UUID or other id of the context (group_id, filiere_id)
            $table->string('context_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'role', 'context_type', 'context_id']);
        });

        // ── notifications table (standard Laravel) ────────────────────────
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->uuidMorphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');

        Schema::table('adhesion_groups', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn('reviewed_by');
        });

        Schema::dropIfExists('notifications');
    }
};
