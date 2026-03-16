<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honeypot_traps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // fake_login, fake_admin, etc.
            $table->string('fake_service')->nullable(); // SSH, FTP, HTTP...
            $table->integer('port')->nullable();
            $table->string('path')->nullable(); // URL path
            $table->string('status')->default('active'); // active, inactive, triggered
            $table->text('description')->nullable();
            $table->text('lure_content')->nullable(); // Contenu appât
            $table->integer('interactions_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('honeypot_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honeypot_trap_id')->constrained('honeypot_traps')->onDelete('cascade');
            $table->string('source_ip');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->string('isp')->nullable();
            $table->string('method')->default('GET'); // HTTP method or action
            $table->string('path')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload')->nullable(); // What they submitted
            $table->json('credentials_attempted')->nullable(); // username/password
            $table->integer('session_duration')->default(0); // seconds
            $table->json('actions_taken')->nullable();
            $table->integer('risk_score')->default(0); // 0-100
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honeypot_interactions');
        Schema::dropIfExists('honeypot_traps');
    }
};
