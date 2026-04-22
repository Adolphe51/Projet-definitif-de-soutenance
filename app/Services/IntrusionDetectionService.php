<?php

namespace App\Services;

use App\Models\Attack;
use App\Models\Alert;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class IntrusionDetectionService
{
    /**
     * Détecte les intrusions basées sur le comportement réel des utilisateurs
     */
    public static function analyzeAuthenticationAttempt(
        string $email,
        string $sourceIp,
        bool $success,
        ?string $reason = null
    ): ?Attack {
        // Récupérer l'utilisateur
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        // Détection d'intrusion : tentatives échouées multiples
        $failedAttempts = self::getFailedAttemptsInWindow($sourceIp, 15); // 15 minutes

        $severity = null;
        $type = null;
        $shouldAlert = false;

        // Analyse du comportement
        if ($failedAttempts >= 5) {
            $type = 'Brute Force';
            $severity = $failedAttempts >= 10 ? 'critical' : 'high';
            $shouldAlert = true;
        }

        // Détection de géolocalisation anormale
        $geo = GeoService::lookup($sourceIp);

        if ($user->last_ip && $sourceIp !== $user->last_ip) {
            $lastGeo = GeoService::lookup($user->last_ip);

            if ($geo && $lastGeo && self::isGeolocationAnomaly($geo, $lastGeo)) {
                if (!$type) {
                    $type = 'Suspicious Login';
                    $severity = 'medium';
                }
                $shouldAlert = true;
            }
        }

        // Si aucune détection, pas d'attaque
        if (!$type) {
            return null;
        }

        // Créer un enregistrement d'attaque
        $attack = Attack::create([
            'type' => $type,
            'source_ip' => $sourceIp,
            'target_ip' => $_SERVER['SERVER_ADDR'] ?? '0.0.0.0',
            'target_port' => $_SERVER['SERVER_PORT'] ?? '443',
            'protocol' => 'TCP',
            'severity' => $severity,
            'status' => 'detected',
            'country' => $geo['country'] ?? 'Unknown',
            'city' => $geo['city'] ?? 'Unknown',
            'latitude' => $geo['lat'] ?? 0,
            'longitude' => $geo['lon'] ?? 0,
            'isp' => $geo['isp'] ?? 'Unknown',
            'packet_count' => $failedAttempts,
            'bandwidth_mbps' => 0.0,
            'description' => "Tentatives échouées: {$failedAttempts}. Raison: {$reason}",
            'is_simulation' => false,
            'alarm_triggered' => in_array($severity, ['high', 'critical']),
        ]);

        if ($shouldAlert) {
            Alert::create([
                'attack_id' => $attack->id,
                'title' => "⚠️ Détection d'intrusion - {$type}",
                'message' => "Utilisateur: {$email} | IP: {$sourceIp} | Tentatives: {$failedAttempts}",
                'severity' => $severity,
                'type' => 'attack',
            ]);
        }

        // Journaliser l'intrusion
        Log::warning('Intrusion detected', [
            'type' => $type,
            'email' => $email,
            'source_ip' => $sourceIp,
            'severity' => $severity,
            'failed_attempts' => $failedAttempts,
            'reason' => $reason,
        ]);

        return $attack;
    }

    /**
     * Compte les tentatives échouées dans une fenêtre de temps
     */
    private static function getFailedAttemptsInWindow(string $sourceIp, int $minutes): int
    {
        return Attack::where('source_ip', $sourceIp)
            ->where('type', 'Brute Force')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Détecte les anomalies de géolocalisation
     */
    private static function isGeolocationAnomaly(array $currentGeo, array $lastGeo): bool
    {
        // Si les pays sont différents, c'est anormal
        if ($currentGeo['country'] !== $lastGeo['country']) {
            return true;
        }

        // Si les villes sont très éloignées (distance > 100 km), c'est anormal
        $distance = self::calculateDistance(
            $currentGeo['lat'],
            $currentGeo['lon'],
            $lastGeo['lat'],
            $lastGeo['lon']
        );

        return $distance > 100; // 100 km
    }

    /**
     * Calcule la distance entre deux points géographiques (Formule de Haversine)
     */
    private static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
