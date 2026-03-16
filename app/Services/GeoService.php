<?php

namespace App\Services;

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

    public static function lookup(string $ip): array
    {
        // Pour la démo, on génère une géoloc aléatoire basée sur l'IP
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
}
