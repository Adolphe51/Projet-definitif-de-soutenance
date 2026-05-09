<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('intranet_attendances')) {
            Schema::drop('intranet_attendances');
        }

        if (Schema::hasTable('intranet_resources')) {
            Schema::drop('intranet_resources');
        }
    }

    public function down(): void
    {
        // Tables retirees du perimetre utile du mini site.
    }
};
