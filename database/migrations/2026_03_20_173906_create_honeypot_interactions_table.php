<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honeypot_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honeypot_trap_id')->constrained('honeypot_traps')->onDelete('cascade');
            $table->string('source_ip');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->string('isp')->nullable();
            $table->string('method')->default('GET');
            $table->string('path')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload')->nullable();
            $table->json('credentials_attempted')->nullable();
            $table->integer('session_duration')->default(0);
            $table->json('actions_taken')->nullable();
            $table->integer('risk_score')->default(0);
            $table->timestamps();

            $table->index('source_ip');
            $table->index('risk_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honeypot_interactions');
    }
};
