<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intranet_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id');
            $table->uuid('recipient_id');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('intranet_students');
            $table->foreign('recipient_id')->references('id')->on('intranet_students');
            $table->index(['sender_id', 'recipient_id']);
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intranet_messages');
    }
};
