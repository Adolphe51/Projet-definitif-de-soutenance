<?php

use App\Enums\AppRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            // Colonne "role" correspondant à AppRole
            $table->string('role')
                ->default(AppRole::Analyst->value)
                ->comment('Identifiant technique du rôle');

            $table->foreignId('permission_id')
                ->constrained()
                ->cascadeOnDelete();

            // Timestamps facultatifs
            $table->timestamps();

            // Clé primaire composée
            $table->primary(['role', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
