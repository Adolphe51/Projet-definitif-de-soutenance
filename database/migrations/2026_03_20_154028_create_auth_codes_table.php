<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_codes', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // code hashé
            $table->string('code_hash');

            // expiration
            $table->timestamp('expires_at');

            $table->string('email');

            // tentative brute force
            $table->unsignedTinyInteger('attempts')
                ->default(0);

            // code utilisé
            $table->timestamp('used_at')
                ->nullable();

            // ip de création
            $table->string('ip_address', 45)
                ->nullable();

            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_codes');
    }
};
