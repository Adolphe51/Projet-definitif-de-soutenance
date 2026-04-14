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
        Schema::create('intranet_courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('course_code', 20)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('department');
            $table->integer('credits')->default(3);
            $table->string('semester', 10);
            $table->integer('max_students')->default(30);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['department', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intranet_courses');
    }
};
