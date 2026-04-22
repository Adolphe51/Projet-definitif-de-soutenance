<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            $table->index(['source_ip', 'type', 'created_at'], 'attacks_source_ip_type_created_at_idx');
            $table->index('created_at', 'attacks_created_at_idx');
            $table->index(['severity', 'status', 'is_simulation'], 'attacks_severity_status_simulation_idx');
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->index('attack_id', 'alerts_attack_id_idx');
            $table->index(['acknowledged', 'created_at'], 'alerts_acknowledged_created_at_idx');
        });

        Schema::table('honeypot_interactions', function (Blueprint $table) {
            $table->index(['honeypot_trap_id', 'created_at'], 'honeypot_interactions_trap_created_at_idx');
        });

        Schema::table('security_sessions', function (Blueprint $table) {
            $table->index('last_activity_at', 'security_sessions_last_activity_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            $table->dropIndex('attacks_source_ip_type_created_at_idx');
            $table->dropIndex('attacks_created_at_idx');
            $table->dropIndex('attacks_severity_status_simulation_idx');
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex('alerts_attack_id_idx');
            $table->dropIndex('alerts_acknowledged_created_at_idx');
        });

        Schema::table('honeypot_interactions', function (Blueprint $table) {
            $table->dropIndex('honeypot_interactions_trap_created_at_idx');
        });

        Schema::table('security_sessions', function (Blueprint $table) {
            $table->dropIndex('security_sessions_last_activity_idx');
        });
    }
};
