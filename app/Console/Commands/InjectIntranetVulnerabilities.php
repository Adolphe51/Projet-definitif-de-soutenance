<?php

namespace App\Console\Commands;

use App\Services\IntranetVulnerabilityService;
use Illuminate\Console\Command;

class InjectIntranetVulnerabilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intranet:vulnerabilities {action : Action to perform (inject|clean|dos|enumeration|xss|bruteforce)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gérer les vulnérabilités de test dans l\'intranet académique';

    protected IntranetVulnerabilityService $vulnerabilityService;

    public function __construct(IntranetVulnerabilityService $vulnerabilityService)
    {
        parent::__construct();
        $this->vulnerabilityService = $vulnerabilityService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        $this->info("🚨 Gestion des vulnérabilités de l'intranet - Action: {$action}");

        switch ($action) {
            case 'inject':
                $this->injectAllVulnerabilities();
                break;

            case 'clean':
                $this->vulnerabilityService->cleanVulnerabilities();
                $this->info('✅ Données de vulnérabilité nettoyées');
                break;

            case 'dos':
                $this->vulnerabilityService->simulateDosAttack();
                $this->info('✅ Attaque DoS simulée');
                break;

            case 'enumeration':
                $this->vulnerabilityService->createUserEnumerationData();
                $this->info('✅ Données d\'énumération d\'utilisateurs créées');
                break;

            case 'xss':
                $this->vulnerabilityService->injectMaliciousCourseData();
                $this->info('✅ Données XSS injectées');
                break;

            case 'bruteforce':
                $this->vulnerabilityService->createBruteForceScenarios();
                $this->info('✅ Scénarios de force brute créés');
                break;

            default:
                $this->error('Action non reconnue. Utilisez: inject, clean, dos, enumeration, xss, ou bruteforce');
                return 1;
        }

        $this->info('🎯 Opération terminée avec succès');
        return 0;
    }

    /**
     * Injecter toutes les vulnérabilités de test.
     */
    protected function injectAllVulnerabilities(): void
    {
        $this->info('Injection des vulnérabilités SQL...');
        $this->vulnerabilityService->injectSqlVulnerabilities();

        $this->info('Création des données d\'énumération...');
        $this->vulnerabilityService->createUserEnumerationData();

        $this->info('Injection des données XSS...');
        $this->vulnerabilityService->injectMaliciousCourseData();

        $this->info('Création des scénarios de force brute...');
        $this->vulnerabilityService->createBruteForceScenarios();

        $this->info('Simulation d\'attaque DoS...');
        $this->vulnerabilityService->simulateDosAttack();

        $this->info('✅ Toutes les vulnérabilités ont été injectées');
    }
}
