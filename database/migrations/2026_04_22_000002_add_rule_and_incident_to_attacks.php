<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            // Ajouter les colonnes si elles n'existent pas
            if (!Schema::hasColumn('attacks', 'rule_id')) {
                $table->string('rule_id')->nullable()->after('type');
                $table->index('rule_id');
            }
            
            if (!Schema::hasColumn('attacks', 'incident_id')) {
                $table->string('incident_id')->nullable()->after('rule_id');
                $table->index('incident_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            if (Schema::hasColumn('attacks', 'rule_id')) {
                $table->dropIndex(['rule_id']);
                $table->dropColumn('rule_id');
            }
            
            if (Schema::hasColumn('attacks', 'incident_id')) {
                $table->dropIndex(['incident_id']);
                $table->dropColumn('incident_id');
            }
        });
    }
};
