<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attacks', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // DDoS, SQL Injection, XSS, etc.
            $table->string('source_ip');
            $table->string('target_ip')->default('192.168.1.1');
            $table->string('target_port')->nullable();
            $table->string('protocol')->default('TCP');
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('detected'); // detected, blocked, investigating
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->string('isp')->nullable();
            $table->unsignedBigInteger('packet_count')->default(0);
            $table->decimal('bandwidth_mbps', 8, 2)->default(0);
            $table->text('payload')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_simulation')->default(false);
            $table->boolean('alarm_triggered')->default(false);
            $table->timestamps();

            // Indexes pour performance
            $table->index(['severity', 'status']);
            $table->index('source_ip');
            $table->index('target_ip');
            $table->index('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attacks');
    }
};
