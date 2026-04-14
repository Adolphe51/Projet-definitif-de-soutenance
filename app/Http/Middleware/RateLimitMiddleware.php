<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Limite les tentatives de connexion et d'OTP par IP
     *
     * 🔐 CORRECTION : Incrémente AVANT de passer la requête (pas après)
     * pour éviter les attaques par force brute.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        
        // Récupérer le nom de la route (avec fallback)
        $routeName = $request->route()?->getName() ?? 'unknown';
        
        // Récupérer la configuration de limitation de débit pour cette route
        $rateLimitConfig = $this->getRateLimitConfig($routeName);
        $maxAttempts = $rateLimitConfig['max_attempts'];
        $decayMinutes = $rateLimitConfig['decay_minutes'];
        
        $key = "rate_limit:{$ip}:{$routeName}";
        
        $attempts = (int) Cache::get($key, 0);
        
        // 🔐 Vérifier AVANT d'incrémenter
        if ($attempts >= $maxAttempts) {
            // Bloquer l'IP temporairement
            $blockKey = "blocked_ip:{$ip}:rate_limit";
            Cache::put($blockKey, true, now()->addMinutes($decayMinutes));
            
            return response()->json([
                'error' => 'Trop de tentatives. Veuillez réessayer dans ' . $decayMinutes . ' minutes.',
                'retry_after' => $decayMinutes * 60,
                'blocked' => true
            ], 429);
        }
        
        // 🔐 Incrémenter AVANT de passer la requête
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        return $next($request);
    }
    
    /**
     * Récupère la configuration de limitation de débit pour une route donnée
     */
    private function getRateLimitConfig(string $routeName): array
    {
        $config = config('cyberguard.rate_limits', []);
        
        // Configuration par défaut
        $defaultConfig = [
            'max_attempts' => 5,
            'decay_minutes' => 15
        ];
        
        // Retourner la configuration spécifique à la route ou la configuration par défaut
        return $config[$routeName] ?? $defaultConfig;
    }
}
