<?php

namespace App\Http\Middleware;

use App\Models\HoneypotTrap;
use App\Models\HoneypotInteraction;
use App\Models\Alert;
use App\Models\BlockedIp;
use App\Services\GeoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HoneypotMiddleware
{
    /**
     * Intercepte et enregistre les requêtes vers les pièges honeypot.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? '127.0.0.1';

        // Ignorer la whitelist
        $whitelist = config('cyberguard.honeypot.whitelist', ['127.0.0.1', '::1']);
        if (in_array($ip, $whitelist)) {
            return $next($request);
        }

        // Vérifier si le honeypot est activé
        if (!config('cyberguard.honeypot.enabled', true)) {
            return $next($request);
        }

        // Vérifier si l'IP est déjà bloquée
        if (BlockedIp::isBlocked($ip)) {
            return response()->view('honeypot.traps.fake_error', [
                'message' => 'Your IP has been permanently blocked.',
            ], 403);
        }

        // Logger la requête dans le système
        $this->logRequest($request, $ip);

        return $next($request);
    }

    private function logRequest(Request $request, string $ip): void
    {
        try {
            $trapPaths = config('cyberguard.honeypot.trap_paths', []);
            $path      = '/' . ltrim($request->path(), '/');
            $trapType  = null;

            foreach ($trapPaths as $trapPath => $type) {
                if (str_starts_with($path, $trapPath)) {
                    $trapType = $type;
                    break;
                }
            }

            if (!$trapType) return;

            $trap = HoneypotTrap::where('type', $trapType)->first();
            if (!$trap) return;

            $geo = GeoService::lookup($ip);

            // Créer l'interaction
            $interaction = HoneypotInteraction::create([
                'honeypot_trap_id' => $trap->id,
                'source_ip'        => $ip,
                'country'          => $geo['country'],
                'city'             => $geo['city'],
                'latitude'         => $geo['lat'],
                'longitude'        => $geo['lon'],
                'isp'              => $geo['isp'],
                'method'           => $request->method(),
                'path'             => $path,
                'user_agent'       => $request->userAgent() ?? 'Unknown',
                'payload'          => json_encode($request->except(['_token', 'password'])),
                'risk_score'       => $this->calculateRiskScore($request),
            ]);

            $trap->increment('interactions_count');
            $trap->update(['last_triggered_at' => now(), 'status' => 'triggered']);

            // Alerte si méthode POST (tentative réelle)
            if ($request->isMethod('post')) {
                Alert::create([
                    'title'    => "🍯 PIÈGE DÉCLENCHÉ: {$trap->name}",
                    'message'  => "{$ip} ({$geo['city']}, {$geo['country']}) accès {$request->method()} → {$path}",
                    'severity' => $interaction->risk_score >= 80 ? 'critical' : 'high',
                    'type'     => 'honeypot',
                ]);
            }

            // Auto-blocage si score très élevé
            if ($interaction->risk_score >= 95) {
                BlockedIp::blockIp($ip, "Auto-bloqué: score honeypot {$interaction->risk_score}/100");
            }

        } catch (\Throwable $e) {
            Log::error('HoneypotMiddleware error: ' . $e->getMessage());
        }
    }

    private function calculateRiskScore(Request $request): int
    {
        $score = 50;
        $ua    = strtolower($request->userAgent() ?? '');

        // Outils malveillants connus
        $maliciousTools = ['sqlmap', 'nikto', 'nmap', 'hydra', 'masscan', 'burpsuite', 'metasploit', 'python-requests', 'curl'];
        foreach ($maliciousTools as $tool) {
            if (str_contains($ua, $tool)) { $score += 30; break; }
        }

        // Méthodes suspectes
        if ($request->isMethod('post'))   $score += 15;
        if ($request->isMethod('delete')) $score += 20;
        if ($request->isMethod('put'))    $score += 10;

        // Payloads suspects
        $body = strtolower($request->getContent());
        if (str_contains($body, 'union select'))    $score += 25;
        if (str_contains($body, 'script'))          $score += 15;
        if (str_contains($body, '../'))             $score += 20;
        if (str_contains($body, 'cmd=') || str_contains($body, 'exec(')) $score += 30;
        if (str_contains($body, 'passwd') || str_contains($body, 'shadow')) $score += 25;

        // Headers suspects
        if (!$request->header('Accept'))            $score += 10;
        if (!$request->header('Accept-Language'))   $score += 5;

        return min(100, $score);
    }
}
