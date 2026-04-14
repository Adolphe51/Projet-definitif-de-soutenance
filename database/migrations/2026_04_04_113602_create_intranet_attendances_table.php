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
        Schema::create('intranet_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('enrollment_id');
            $table->date('lecture_date');
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('enrollment_id')->references('id')->on('intranet_enrollments');
            $table->index(['enrollment_id', 'lecture_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intranet_attendances');
    }
};
