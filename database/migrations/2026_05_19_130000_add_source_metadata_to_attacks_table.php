<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            $table->string('source_scope', 20)->nullable()->after('status');
            $table->string('source_channel', 30)->nullable()->after('source_scope');
            $table->string('source_label')->nullable()->after('source_channel');
            $table->boolean('is_geolocatable')->nullable()->after('source_label');

            $table->index(['source_scope', 'source_channel'], 'attacks_source_scope_channel_idx');
            $table->index('is_geolocatable', 'attacks_is_geolocatable_idx');
        });
    }

    public function down(): void
    {
        Schema::table('attacks', function (Blueprint $table) {
            $table->dropIndex('attacks_source_scope_channel_idx');
            $table->dropIndex('attacks_is_geolocatable_idx');
            $table->dropColumn(['source_scope', 'source_channel', 'source_label', 'is_geolocatable']);
        });
    }
};
