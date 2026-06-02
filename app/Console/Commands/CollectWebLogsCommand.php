<?php

namespace App\Console\Commands;

use App\Services\WebLogIngestionService;
use Illuminate\Console\Command;

class CollectWebLogsCommand extends Command
{
    protected $signature = 'cyberguard:collect-web-logs
                            {--file= : Chemin du access.log a analyser}
                            {--reset-offset : Rejouer le fichier depuis le debut}';

    protected $description = 'Ingere un journal d acces web pour detecter des tests reels contre CyberGuard';

    public function __construct(
        private readonly WebLogIngestionService $webLogIngestionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->option('file') ?: config('cyberguard.detection.log_ingestion.access_log_path');

        if (!$path) {
            $this->warn('Aucun fichier configure. Utilisez --file ou CYBERGUARD_WEB_ACCESS_LOG_PATH.');

            return Command::FAILURE;
        }

        $stats = $this->webLogIngestionService->ingestFile($path, (bool) $this->option('reset-offset'));

        if ($stats['errors'] > 0) {
            $this->error("Lecture impossible: {$path}");

            return Command::FAILURE;
        }

        $this->info("Journal analyse: {$stats['path']}");
        $this->line("Lignes traitees : {$stats['processed']}");
        $this->line("Detections creees: {$stats['detected']}");
        $this->line("Lignes ignorees : {$stats['skipped']}");

        return Command::SUCCESS;
    }
}
