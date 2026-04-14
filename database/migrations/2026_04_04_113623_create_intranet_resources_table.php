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
        Schema::create('intranet_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->string('title');
            $table->string('file_path', 500);
            $table->string('file_type', 50);
            $table->uuid('uploaded_by');
            $table->timestamp('uploaded_at');
            $table->integer('access_count')->default(0);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('intranet_courses');
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->index(['course_id', 'uploaded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intranet_resources');
    }
};
