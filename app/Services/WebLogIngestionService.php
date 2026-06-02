<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class WebLogIngestionService
{
    public function ingestFile(string $path, bool $resetOffset = false): array
    {
        $stats = [
            'path' => $path,
            'processed' => 0,
            'detected' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        if (!is_file($path) || !is_readable($path)) {
            $stats['errors']++;

            return $stats;
        }

        $offsetKey = $this->offsetCacheKey($path);
        if ($resetOffset) {
            Cache::forget($offsetKey);
        }

        $offset = max(0, (int) Cache::get($offsetKey, 0));
        $size = filesize($path) ?: 0;

        if ($size < $offset) {
            $offset = 0;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $stats['errors']++;

            return $stats;
        }

        if ($offset > 0) {
            fseek($handle, $offset);
        }

        $limit = max(1, (int) config('cyberguard.detection.log_ingestion.max_lines_per_run', 500));

        while (($line = fgets($handle)) !== false) {
            $stats['processed']++;

            $detected = $this->processLine($line);
            if ($detected) {
                $stats['detected']++;
            } else {
                $stats['skipped']++;
            }

            if ($stats['processed'] >= $limit) {
                break;
            }
        }

        $newOffset = ftell($handle);
        fclose($handle);

        Cache::put(
            $offsetKey,
            $newOffset === false ? $size : $newOffset,
            now()->addHours((int) config('cyberguard.detection.log_ingestion.offset_ttl_hours', 24))
        );

        return $stats;
    }

    private function processLine(string $line): bool
    {
        $entry = $this->parseLine($line);
        if ($entry === null) {
            return false;
        }

        $ip = $entry['ip'];
        if (in_array($ip, config('cyberguard.detection.auto_block.allowlist', ['127.0.0.1', '::1']), true)) {
            return false;
        }

        $analysis = $this->analyzeEntry($entry);
        if ($analysis === null) {
            return false;
        }

        $cooldown = max(10, (int) config('cyberguard.detection.log_ingestion.cooldown_seconds', 60));
        $fingerprint = sha1(implode('|', [
            $analysis['rule_id'],
            $ip,
            $analysis['tool'] ?? '',
            $entry['method'],
            $entry['path'],
        ]));

        if (!Cache::add("cyberguard:web-log:detection:{$fingerprint}", true, now()->addSeconds($cooldown))) {
            return false;
        }

        $context = [
            'source_ip' => $ip,
            'target_ip' => $this->resolveTargetHost(),
            'target_port' => $this->resolveTargetPort(),
            'protocol' => 'HTTP',
            'severity' => $analysis['severity'],
            'packet_count' => 1,
            'payload' => json_encode([
                'method' => $entry['method'],
                'path' => $entry['path'],
                'query' => $entry['query'],
                'status' => $entry['status'],
                'user_agent' => $entry['user_agent'],
                'tool' => $analysis['tool'],
                'source' => 'web_access_log',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'description' => $analysis['description'],
            'source_channel' => 'network',
            'source_scope' => AttackDetectionService::isPrivateOrReservedIp($ip) ? 'internal' : 'external',
            'source_label' => 'Journal web',
            'prefer_real_geo' => false,
        ];

        return match ($analysis['rule_id']) {
            'sql_injection' => app(AttackDetectionService::class)->detectAttack('SQL Injection', $context) !== null,
            'xss_attempt' => app(AttackDetectionService::class)->detectAttack('XSS', $context) !== null,
            'http_recon_scan' => AttackDetectionService::generateAttackByRule('http_recon_scan', $context) !== null,
            default => false,
        };
    }

    private function parseLine(string $line): ?array
    {
        $pattern = '/^(?<ip>\S+)\s+\S+\s+\S+\s+\[(?<timestamp>[^\]]+)\]\s+"(?<method>[A-Z]+)\s+(?<target>\S+)(?:\s+HTTP\/[0-9.]+)?"\s+(?<status>\d{3})\s+(?<bytes>\S+)(?:\s+"(?<referer>[^"]*)"\s+"(?<ua>[^"]*)")?/';

        if (!preg_match($pattern, trim($line), $matches)) {
            return null;
        }

        $target = $matches['target'] ?? '/';
        $parts = parse_url($target);

        return [
            'ip' => $matches['ip'],
            'timestamp' => $matches['timestamp'] ?? null,
            'method' => strtoupper($matches['method']),
            'path' => $parts['path'] ?? '/',
            'query' => $parts['query'] ?? '',
            'status' => (int) ($matches['status'] ?? 0),
            'bytes' => $matches['bytes'] ?? '0',
            'referer' => $matches['referer'] ?? '',
            'user_agent' => $matches['ua'] ?? '',
            'raw_target' => $target,
        ];
    }

    private function analyzeEntry(array $entry): ?array
    {
        $userAgent = strtolower($entry['user_agent']);
        $path = strtolower($entry['path']);
        $query = strtolower(urldecode($entry['query']));
        $fullTarget = strtolower(urldecode($entry['raw_target']));
        $tool = $this->resolveTool($userAgent);

        if ($tool === 'sqlmap' || $this->containsSignature($fullTarget, config('cyberguard.detection.log_ingestion.sql_signatures', []))) {
            return [
                'rule_id' => 'sql_injection',
                'tool' => $tool ?: 'sqlmap-like',
                'severity' => 'high',
                'description' => "Pattern SQL suspect detecte via journal web sur {$entry['method']} {$entry['path']} depuis {$entry['ip']}",
            ];
        }

        if ($this->containsSignature($fullTarget, config('cyberguard.detection.log_ingestion.xss_signatures', []))) {
            return [
                'rule_id' => 'xss_attempt',
                'tool' => $tool ?: 'xss-like',
                'severity' => 'medium',
                'description' => "Pattern XSS suspect detecte via journal web sur {$entry['method']} {$entry['path']} depuis {$entry['ip']}",
            ];
        }

        $suspiciousPath = $this->containsSignature($path, config('cyberguard.detection.log_ingestion.sensitive_paths', []));
        $reconStatus = in_array($entry['status'], [401, 403, 404], true);
        $minimalHeadersTool = $tool !== null;
        $traversal = $this->containsSignature($query, config('cyberguard.detection.log_ingestion.traversal_signatures', []))
            || $this->containsSignature($path, config('cyberguard.detection.log_ingestion.traversal_signatures', []));

        if ($minimalHeadersTool || $suspiciousPath || $reconStatus || $traversal) {
            $label = $tool ? "signature {$tool}" : 'enumeration web';

            return [
                'rule_id' => 'http_recon_scan',
                'tool' => $tool ?: 'recon',
                'severity' => $tool || $suspiciousPath ? 'high' : 'medium',
                'description' => "Reconnaissance HTTP detectee via journal web ({$label}) sur {$entry['method']} {$entry['path']} depuis {$entry['ip']}",
            ];
        }

        return null;
    }

    private function resolveTool(string $userAgent): ?string
    {
        foreach (config('cyberguard.detection.log_ingestion.tool_signatures', []) as $tool => $signatures) {
            foreach ((array) $signatures as $signature) {
                $needle = strtolower((string) $signature);

                if ($needle !== '' && str_contains($userAgent, $needle)) {
                    return (string) $tool;
                }
            }
        }

        return null;
    }

    private function containsSignature(string $value, array $signatures): bool
    {
        foreach ($signatures as $signature) {
            $needle = strtolower((string) $signature);

            if ($needle !== '' && str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function resolveTargetHost(): string
    {
        return parse_url((string) config('app.url'), PHP_URL_HOST) ?: '127.0.0.1';
    }

    private function resolveTargetPort(): int
    {
        $port = parse_url((string) config('app.url'), PHP_URL_PORT);
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);

        if (is_int($port)) {
            return $port;
        }

        return $scheme === 'https' ? 443 : 80;
    }

    private function offsetCacheKey(string $path): string
    {
        return 'cyberguard:web-log:offset:' . sha1($path);
    }
}
