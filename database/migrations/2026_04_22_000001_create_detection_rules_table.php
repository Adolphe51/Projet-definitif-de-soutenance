<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_id')->unique(); // Ex: 'brute_force_ssh', 'port_scan_icmp'
            $table->string('name'); // Ex: 'SSH Brute Force'
            $table->text('description')->nullable();
            
            // Type et sévérité
            $table->string('attack_type'); // DDoS, SQL Injection, Brute Force, etc.
            $table->string('default_severity')->default('medium'); // low, medium, high, critical
            
            // Paramètres de détection (JSON pour flexibilité)
            $table->json('detection_params')->nullable(); // Ex: {"threshold": 5, "window_minutes": 10}
            
            // Statut
            $table->boolean('enabled')->default(true);
            $table->integer('priority')->default(10); // Plus bas = plus haut priorité
            
            // Métadonnées
            $table->string('category')->nullable(); // auth, network, application, etc.
            $table->text('cve_references')->nullable(); // CVE links
            
            $table->timestamps();
            
            $table->index(['enabled', 'attack_type']);
            $table->index('attack_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_rules');
    }
};
