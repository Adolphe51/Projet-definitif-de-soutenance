<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {

            $table->id();

            $table->string('nom')
                ->unique()
                ->comment('Identifiant unique de la permission, ex: user.create');

            $table->string('description')
                ->nullable()
                ->comment('Description lisible de la permission, ex: "Créer un utilisateur"');

            $table->string('ressourceType')
                ->nullable()
                ->comment('Type de ressource protégée');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
