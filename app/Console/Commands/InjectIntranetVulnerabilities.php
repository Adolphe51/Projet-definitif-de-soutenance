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
    protected $signature = 'intranet:vulnerabilities {action? : Action to perform (inject|clean|sql|xss|bruteforce)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gérer les scénarios de test du mini site relié à CyberGuard';

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
        $action = (string) ($this->argument('action') ?? '');

        if ($action === '') {
            $this->components->info('Actions disponibles : inject, clean, sql, xss, bruteforce');
            $this->line('Exemple : php artisan intranet:vulnerabilities inject');

            return self::SUCCESS;
        }

        $this->info("Gestion des scenarios de test du mini site - Action: {$action}");

        switch ($action) {
            case 'inject':
                $this->injectAllVulnerabilities();
                break;

            case 'sql':
                $this->vulnerabilityService->injectSqlVulnerabilities();
                $this->info('Donnees SQL injectees');
                break;

            case 'clean':
                $this->vulnerabilityService->cleanVulnerabilities();
                $this->info('Donnees de vulnerabilite nettoyees');
                break;

            case 'xss':
                $this->vulnerabilityService->injectMaliciousCourseData();
                $this->info('Donnees XSS injectees');
                break;

            case 'bruteforce':
                $this->vulnerabilityService->createBruteForceScenarios();
                $this->info('Scenarios de force brute crees');
                break;

            default:
                $this->error('Action non reconnue. Utilisez: inject, clean, sql, xss, ou bruteforce');
                return self::FAILURE;
        }

        $this->info('Operation terminee avec succes');
        return self::SUCCESS;
    }

    /**
     * Injecter toutes les vulnérabilités de test.
     */
    protected function injectAllVulnerabilities(): void
    {
        $this->info('Injection des vulnerabilites SQL...');
        $this->vulnerabilityService->injectSqlVulnerabilities();

        $this->info('Injection des donnees XSS...');
        $this->vulnerabilityService->injectMaliciousCourseData();

        $this->info('Creation des scenarios de force brute...');
        $this->vulnerabilityService->createBruteForceScenarios();

        $this->info('Toutes les vulnerabilites ont ete injectees');
    }
}
