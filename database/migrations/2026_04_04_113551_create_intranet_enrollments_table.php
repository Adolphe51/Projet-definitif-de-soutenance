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
        Schema::create('intranet_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('course_id');
            $table->string('semester', 10);
            $table->timestamp('enrollment_date');
            $table->string('grade', 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('status', ['enrolled', 'completed', 'dropped'])->default('enrolled');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('intranet_students');
            $table->foreign('course_id')->references('id')->on('intranet_courses');
            $table->index(['student_id', 'course_id']);
            $table->index(['semester', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intranet_enrollments');
    }
};
