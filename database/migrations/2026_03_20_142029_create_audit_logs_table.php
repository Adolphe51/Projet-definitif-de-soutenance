<?php

use App\Enums\AuditImportance;
use App\Enums\AuditResult;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {

            // UUID compatible avec HasUuids
            $table->uuid('id')->primary();

            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();

            $table->string('previous_hash', 64)->nullable();
            $table->string('current_hash', 64)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('metadata')->nullable();

            $table->string('ressource');

            // Enum → string pour compatibilité SQLite
            $table->string('resultat')
                ->default(AuditResult::Erreur->value);

            $table->string('importance')
                ->default(AuditImportance::Moyenne->value);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(['entity_type', 'entity_id']);
            $table->index('actor_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
