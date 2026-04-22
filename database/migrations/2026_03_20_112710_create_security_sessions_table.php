<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('access_token_hash', 64)->unique();

            $table->string('refresh_token_hash', 64)->nullable()->unique();

            $table->ipAddress('ip_address')->nullable();

            $table->text('user_agent')->nullable();

            $table->string('device_fingerprint')->nullable();

            $table->timestamp('expires_at');

            $table->boolean('is_revoked')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'is_revoked', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_sessions');
    }
};
