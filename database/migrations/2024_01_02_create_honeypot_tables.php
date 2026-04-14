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
            $table->string('type');
            $table->string('fake_service')->nullable();
            $table->integer('port')->nullable();
            $table->string('path')->nullable();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->text('lure_content')->nullable();
            $table->integer('interactions_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honeypot_traps');
    }
};
