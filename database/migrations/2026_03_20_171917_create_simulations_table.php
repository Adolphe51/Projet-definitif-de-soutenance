<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('attack_type');
            $table->string('target_ip');
            $table->integer('duration_seconds')->default(30);
            $table->string('intensity')->default('medium');
            $table->string('status')->default('pending'); // pending, running, completed, stopped
            $table->integer('packets_sent')->default(0);
            $table->text('log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'attack_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
