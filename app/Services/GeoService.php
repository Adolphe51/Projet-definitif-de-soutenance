<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoService
{
    // Données de géolocalisation simulées réalistes pour la démo
    private static array $geoDatabase = [
        // Asie
        ['country' => 'Chine',          'city' => 'Beijing',      'lat' => 39.9042,  'lon' => 116.4074, 'isp' => 'China Telecom'],
        ['country' => 'Chine',          'city' => 'Shanghai',     'lat' => 31.2304,  'lon' => 121.4737, 'isp' => 'China Unicom'],
        ['country' => 'Russie',         'city' => 'Moscou',       'lat' => 55.7558,  'lon' => 37.6176,  'isp' => 'Rostelecom'],
        ['country' => 'Russie',         'city' => 'Saint-Pétersbourg', 'lat' => 59.9311, 'lon' => 30.3609, 'isp' => 'TTK'],
        ['country' => 'Corée du Nord',  'city' => 'Pyongyang',    'lat' => 39.0385,  'lon' => 125.7625, 'isp' => 'Star JV'],
        ['country' => 'Iran',           'city' => 'Téhéran',      'lat' => 35.6892,  'lon' => 51.3890,  'isp' => 'TCI'],
        ['country' => 'Inde',           'city' => 'Mumbai',       'lat' => 19.0760,  'lon' => 72.8777,  'isp' => 'Reliance Jio'],
        // Europe
        ['country' => 'Roumanie',       'city' => 'Bucarest',     'lat' => 44.4268,  'lon' => 26.1025,  'isp' => 'RDS & RCS'],
        ['country' => 'Ukraine',        'city' => 'Kyiv',         'lat' => 50.4501,  'lon' => 30.5234,  'isp' => 'Ukrtelecom'],
        ['country' => 'Pays-Bas',       'city' => 'Amsterdam',    'lat' => 52.3676,  'lon' => 4.9041,   'isp' => 'Leaseweb'],
        ['country' => 'Allemagne',      'city' => 'Berlin',       'lat' => 52.5200,  'lon' => 13.4050,  'isp' => 'Hetzner'],
        ['country' => 'France',         'city' => 'Paris',        'lat' => 48.8566,  'lon' => 2.3522,   'isp' => 'OVH'],
        // Amérique
        ['country' => 'États-Unis',     'city' => 'New York',     'lat' => 40.7128,  'lon' => -74.0060, 'isp' => 'Verizon'],
        ['country' => 'États-Unis',     'city' => 'Los Angeles',  'lat' => 34.0522,  'lon' => -118.2437,'isp' => 'AT&T'],
        ['country' => 'Brésil',         'city' => 'São Paulo',    'lat' => -23.5505, 'lon' => -46.6333, 'isp' => 'Claro'],
        ['country' => 'Mexique',        'city' => 'Mexico City',  'lat' => 19.4326,  'lon' => -99.1332, 'isp' => 'Telmex'],
        // Afrique
        ['country' => 'Nigeria',        'city' => 'Lagos',        'lat' => 6.5244,   'lon' => 3.3792,   'isp' => 'MTN Nigeria'],
        ['country' => 'Afrique du Sud', 'city' => 'Johannesburg', 'lat' => -26.2041, 'lon' => 28.0473,  'isp' => 'Telkom SA'],
        // Moyen Orient
        ['country' => 'Turquie',        'city' => 'Istanbul',     'lat' => 41.0082,  'lon' => 28.9784,  'isp' => 'Türk Telekom'],
        ['country' => 'Arabie Saoudite','city' => 'Riyad',        'lat' => 24.7136,  'lon' => 46.6753,  'isp' => 'STC'],
    ];

    public static function lookup(string $ip, bool $preferRealProvider = true): array
    {
        if (!$preferRealProvider) {
            return self::lookupSimulated($ip);
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return self::lookupUnknown($ip);
        }

        if (!self::isPublicIp($ip)) {
            return self::lookupPrivateNetwork($ip);
        }

        $provider = (string) config('cyberguard.geo.provider', 'auto');

        if (in_array($provider, ['auto', 'ipapi', 'ipgeolocation'], true)) {
            $cacheKey = self::cacheKey($provider, $ip);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached === false ? self::lookupUnknown($ip) : $cached;
            }

            $resolved = self::resolveRealLocation($provider, $ip);
            Cache::put(
                $cacheKey,
                $resolved ?? false,
                now()->addSeconds((int) config('cyberguard.geo.cache_ttl', 3600))
            );

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return self::lookupUnknown($ip);
    }

    public static function lookupSimulated(string $ip): array
    {
        $index = abs(crc32($ip)) % count(self::$geoDatabase);
        return self::$geoDatabase[$index];
    }

    public static function generateRandomIp(): string
    {
        $ranges = [
            '185.%d.%d.%d',   // Europe/Russia
            '103.%d.%d.%d',   // Asia
            '45.%d.%d.%d',    // Mixed
            '194.%d.%d.%d',   // Europe
            '91.%d.%d.%d',    // Russia/Ukraine
            '123.%d.%d.%d',   // China
            '222.%d.%d.%d',   // China
        ];

        $pattern = $ranges[array_rand($ranges)];
        return sprintf($pattern, rand(1, 254), rand(1, 254), rand(1, 254));
    }

    public static function isHighRiskCountry(string $country): bool
    {
        $highRisk = ['Chine', 'Russie', 'Corée du Nord', 'Iran'];
        return in_array($country, $highRisk);
    }

    public static function getAllGeoData(): array
    {
        return self::$geoDatabase;
    }

    private static function resolveRealLocation(string $provider, string $ip): ?array
    {
        try {
            return match ($provider) {
                'ipgeolocation' => self::resolveViaIpGeolocation($ip),
                'ipapi', 'auto' => self::resolveViaIpApi($ip) ?? self::resolveViaIpGeolocation($ip),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    private static function resolveViaIpApi(string $ip): ?array
    {
        $response = Http::timeout((int) config('cyberguard.geo.timeout', 3))
            ->acceptJson()
            ->get("https://ipapi.co/{$ip}/json/");

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        if (!is_array($data) || isset($data['error'])) {
            return null;
        }

        return self::normalize([
            'country' => $data['country_name'] ?? null,
            'city' => $data['city'] ?? null,
            'lat' => $data['latitude'] ?? null,
            'lon' => $data['longitude'] ?? null,
            'isp' => $data['org'] ?? $data['asn'] ?? null,
        ]);
    }

    private static function resolveViaIpGeolocation(string $ip): ?array
    {
        $apiKey = config('cyberguard.geo.api_key');

        if (!$apiKey) {
            return null;
        }

        $response = Http::timeout((int) config('cyberguard.geo.timeout', 3))
            ->acceptJson()
            ->get('https://api.ipgeolocation.io/ipgeo', [
                'apiKey' => $apiKey,
                'ip' => $ip,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        if (!is_array($data)) {
            return null;
        }

        return self::normalize([
            'country' => $data['country_name'] ?? null,
            'city' => $data['city'] ?? null,
            'lat' => $data['latitude'] ?? null,
            'lon' => $data['longitude'] ?? null,
            'isp' => $data['isp'] ?? $data['organization'] ?? null,
        ]);
    }

    private static function lookupPrivateNetwork(string $ip): array
    {
        return [
            'country' => 'Reseau local',
            'city' => 'Laboratoire local',
            'lat' => null,
            'lon' => null,
            'isp' => "Adresse privee ({$ip})",
        ];
    }

    private static function lookupUnknown(string $ip): array
    {
        return [
            'country' => 'Inconnu',
            'city' => 'Inconnue',
            'lat' => null,
            'lon' => null,
            'isp' => "Resolution indisponible ({$ip})",
        ];
    }

    private static function normalize(array $data): ?array
    {
        if (empty($data['country']) && empty($data['city']) && empty($data['isp'])) {
            return null;
        }

        return [
            'country' => $data['country'] ?: 'Inconnu',
            'city' => $data['city'] ?: 'Inconnue',
            'lat' => is_numeric($data['lat']) ? (float) $data['lat'] : null,
            'lon' => is_numeric($data['lon']) ? (float) $data['lon'] : null,
            'isp' => $data['isp'] ?: 'Inconnu',
        ];
    }

    private static function isPublicIp(string $ip): bool
    {
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private static function cacheKey(string $provider, string $ip): string
    {
        return "geo_lookup:{$provider}:{$ip}";
    }
}
