<?php

namespace App\Http\Middleware;

use App\Services\AttackDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class NetworkReconDetectionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->inspect($request, $response);

        return $response;
    }

    private function inspect(Request $request, Response $response): void
    {
        $ip = $request->ip() ?? '127.0.0.1';

        if ($this->shouldIgnore($ip)) {
            return;
        }

        $config = config('cyberguard.detection.recon', []);
        $windowMinutes = max(1, (int) ($config['window_minutes'] ?? 3));
        $cooldownSeconds = max(30, (int) ($config['cooldown_seconds'] ?? 120));
        $expiresAt = now()->addMinutes($windowMinutes);
        $path = '/' . ltrim($request->path(), '/');
        $normalizedPath = strtolower($path);
        $userAgent = strtolower($request->userAgent() ?? '');
        $accept = strtolower($request->header('Accept', ''));
        $statusCode = $response->getStatusCode();

        $requestCount = $this->incrementCounter("cyberguard:recon:req:{$ip}", $expiresAt);
        $notFoundCount = $statusCode === 404
            ? $this->incrementCounter("cyberguard:recon:404:{$ip}", $expiresAt)
            : (int) Cache::get("cyberguard:recon:404:{$ip}", 0);
        $distinctPaths = $this->recordDistinctPath($ip, $normalizedPath, $expiresAt);
        $sensitivePathCount = $this->isSensitivePath($normalizedPath, $config)
            ? $this->incrementCounter("cyberguard:recon:sensitive:{$ip}", $expiresAt)
            : (int) Cache::get("cyberguard:recon:sensitive:{$ip}", 0);

        $signals = $this->collectSignals($request, $statusCode, $userAgent, $accept, $normalizedPath, $config);

        if (!$this->shouldTrigger($signals, $requestCount, $distinctPaths, $notFoundCount, $sensitivePathCount, $config)) {
            return;
        }

        $reportKey = "cyberguard:recon:reported:{$ip}";
        if (!Cache::add($reportKey, true, now()->addSeconds($cooldownSeconds))) {
            return;
        }

        $severity = $this->resolveSeverity($signals, $distinctPaths, $notFoundCount, $sensitivePathCount);
        $description = sprintf(
            'Reconnaissance HTTP suspecte depuis %s: %s | requetes=%d, chemins=%d, 404=%d, chemins_sensibles=%d',
            $ip,
            implode(', ', $signals),
            $requestCount,
            $distinctPaths,
            $notFoundCount,
            $sensitivePathCount
        );

        AttackDetectionService::generateAttackByRule('http_recon_scan', [
            'source_ip' => $ip,
            'target_ip' => $request->server('SERVER_ADDR') ?? $request->getHost(),
            'target_port' => $request->getPort(),
            'protocol' => 'HTTP',
            'severity' => $severity,
            'status' => 'detected',
            'packet_count' => $requestCount,
            'payload' => json_encode([
                'path' => $path,
                'method' => $request->method(),
                'status_code' => $statusCode,
                'user_agent' => $request->userAgent(),
                'distinct_paths' => $distinctPaths,
                'not_found_count' => $notFoundCount,
                'sensitive_path_count' => $sensitivePathCount,
                'signals' => $signals,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'description' => $description,
            'source_channel' => 'network',
            'source_scope' => AttackDetectionService::isPrivateOrReservedIp($ip) ? 'internal' : 'external',
            'source_label' => 'Reconnaissance HTTP',
            'prefer_real_geo' => false,
        ]);
    }

    private function shouldIgnore(string $ip): bool
    {
        $allowlist = config('cyberguard.detection.auto_block.allowlist', ['127.0.0.1', '::1']);

        return in_array($ip, $allowlist, true);
    }

    private function collectSignals(
        Request $request,
        int $statusCode,
        string $userAgent,
        string $accept,
        string $path,
        array $config
    ): array {
        $signals = [];

        if ($this->containsScannerSignature($userAgent, $config)) {
            $signals[] = 'signature_scanner';
        }

        if (in_array($request->method(), ['HEAD', 'OPTIONS', 'TRACE'], true)) {
            $signals[] = 'methode_reconnaissance';
        }

        if ($this->isSensitivePath($path, $config)) {
            $signals[] = 'chemin_sensible';
        }

        if ($statusCode === 404) {
            $signals[] = 'enumeration_404';
        }

        if ($accept === '' || $accept === '*/*') {
            $signals[] = 'headers_minimaux';
        }

        if (!$request->header('Accept-Language')) {
            $signals[] = 'absence_accept_language';
        }

        return array_values(array_unique($signals));
    }

    private function shouldTrigger(
        array $signals,
        int $requestCount,
        int $distinctPaths,
        int $notFoundCount,
        int $sensitivePathCount,
        array $config
    ): bool {
        if (in_array('signature_scanner', $signals, true)) {
            return true;
        }

        if ($distinctPaths >= (int) ($config['distinct_paths_threshold'] ?? 4)) {
            return true;
        }

        if ($notFoundCount >= (int) ($config['not_found_threshold'] ?? 3)
            && $requestCount >= (int) ($config['request_threshold'] ?? 4)) {
            return true;
        }

        return $sensitivePathCount >= (int) ($config['sensitive_path_threshold'] ?? 2);
    }

    private function resolveSeverity(
        array $signals,
        int $distinctPaths,
        int $notFoundCount,
        int $sensitivePathCount
    ): string {
        if (in_array('signature_scanner', $signals, true) && ($distinctPaths >= 4 || $sensitivePathCount >= 1)) {
            return 'high';
        }

        if ($distinctPaths >= 6 || $notFoundCount >= 5 || $sensitivePathCount >= 2) {
            return 'high';
        }

        return 'medium';
    }

    private function containsScannerSignature(string $userAgent, array $config): bool
    {
        foreach ($config['suspicious_user_agents'] ?? [] as $signature) {
            if ($signature !== '' && str_contains($userAgent, strtolower((string) $signature))) {
                return true;
            }
        }

        return false;
    }

    private function isSensitivePath(string $path, array $config): bool
    {
        foreach ($config['sensitive_paths'] ?? [] as $sensitivePath) {
            $normalizedSensitivePath = strtolower((string) $sensitivePath);

            if ($normalizedSensitivePath !== '' && str_starts_with($path, $normalizedSensitivePath)) {
                return true;
            }
        }

        return false;
    }

    private function incrementCounter(string $key, \DateTimeInterface $expiresAt): int
    {
        Cache::add($key, 0, $expiresAt);
        Cache::increment($key);

        return (int) Cache::get($key, 0);
    }

    private function recordDistinctPath(string $ip, string $path, \DateTimeInterface $expiresAt): int
    {
        $key = "cyberguard:recon:paths:{$ip}";
        $paths = Cache::get($key, []);

        if (!in_array($path, $paths, true)) {
            $paths[] = $path;
            Cache::put($key, array_slice($paths, -25), $expiresAt);
        }

        return count($paths);
    }
}
