<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_ip')->nullable()->comment('Dernière adresse IP');
            $table->timestamp('last_login')->nullable()->comment('Dernière connexion');
            $table->integer('login_attempts')->default(0)->comment('Nombre de tentatives échouées');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_ip', 'last_login', 'login_attempts']);
        });
    }
};
