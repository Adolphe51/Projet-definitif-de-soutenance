<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\DetectionRuleEngine;

class DetectionRuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📋 Initialisation des règles de détection...');
        DetectionRuleEngine::initializeDefaultRules();
        $this->command->info('✅ ' . \App\Models\DetectionRule::count() . ' règles créées/mises à jour');
    }
}
