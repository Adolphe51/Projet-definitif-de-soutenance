<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AppRole;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('role')
                ->default(AppRole::Analyst->value)
                ->comment('Identifiant technique du rôle');

            /*
            |---------------------------------------------
            | Empêche la duplication
            |---------------------------------------------
            */
            $table->primary(['user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
