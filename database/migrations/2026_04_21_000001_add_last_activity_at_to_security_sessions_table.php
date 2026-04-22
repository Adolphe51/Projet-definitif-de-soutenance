<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('security_sessions', 'last_activity_at')) {
            Schema::table('security_sessions', function (Blueprint $table) {
                $table->timestamp('last_activity_at')->nullable()->after('expires_at');
            });
        }

        DB::table('security_sessions')
            ->whereNull('last_activity_at')
            ->update(['last_activity_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('security_sessions', 'last_activity_at')) {
            Schema::table('security_sessions', function (Blueprint $table) {
                $table->dropColumn('last_activity_at');
            });
        }
    }
};
