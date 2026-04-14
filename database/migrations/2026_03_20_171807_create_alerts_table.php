<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->index(['severity', 'acknowledged']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
