<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attacks', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // DDoS, SQLi, XSS, Brute Force, Port Scan, etc.
            $table->string('source_ip');
            $table->string('target_ip')->default('192.168.1.1');
            $table->string('target_port')->nullable();
            $table->string('protocol')->default('TCP');
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('detected'); // detected, blocked, investigating
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->string('isp')->nullable();
            $table->integer('packet_count')->default(0);
            $table->float('bandwidth_mbps')->default(0);
            $table->text('payload')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_simulation')->default(false);
            $table->boolean('alarm_triggered')->default(false);
            $table->timestamps();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attack_id')->nullable()->constrained('attacks')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('severity')->default('medium');
            $table->string('type')->default('attack'); // attack, system, simulation
            $table->boolean('acknowledged')->default(false);
            $table->boolean('sound_played')->default(false);
            $table->timestamps();
        });

        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('attack_type');
            $table->string('target_ip');
            $table->integer('duration_seconds')->default(30);
            $table->string('intensity')->default('medium'); // low, medium, high
            $table->string('status')->default('pending'); // pending, running, completed, stopped
            $table->integer('packets_sent')->default(0);
            $table->text('log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->string('reason')->nullable();
            $table->foreignId('attack_id')->nullable()->constrained('attacks');
            $table->timestamp('blocked_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('simulations');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('attacks');
    }
};
