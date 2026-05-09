<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            // Keep this migration additive and avoid duplicating indexes defined earlier.
            $table->index(['status', 'created_at'], 'attacks_status_created_at_idx');
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->index('created_at', 'alerts_created_at_idx');
        });

        Schema::table('honeypot_interactions', function (Blueprint $table) {
            $table->index('created_at', 'honeypot_interactions_created_at_idx');
        });

        Schema::table('blocked_ips', function (Blueprint $table) {
            $table->index('blocked_until', 'blocked_ips_blocked_until_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            $table->dropIndex('attacks_status_created_at_idx');
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex('alerts_created_at_idx');
        });

        Schema::table('honeypot_interactions', function (Blueprint $table) {
            $table->dropIndex('honeypot_interactions_created_at_idx');
        });

        Schema::table('blocked_ips', function (Blueprint $table) {
            $table->dropIndex('blocked_ips_blocked_until_idx');
        });
    }
};
