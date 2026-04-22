<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CyberGuard Platform Configuration
    |--------------------------------------------------------------------------
    */

    'name'    => env('APP_NAME', 'CyberGuard'),
    'version' => '2.0.0',

    /*
    |--------------------------------------------------------------------------
    | Authentification
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'otp' => [
            'code_length' => 8,
            'ttl_minutes' => 3,
            'max_attempts' => 3,
            'resend_delay_seconds' => 180,
            'pending_auth_ttl_minutes' => 10,
        ],
        'sessions' => [
            'max_active' => 5,
            'ttl_hours' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Détection d'Attaques
    |--------------------------------------------------------------------------
    */
    'detection' => [
        // Seuil de sévérité déclenchant l'alarme sonore automatiquement
        'alarm_threshold' => env('ALARM_THRESHOLD', 'high'),

        // Intervalle de scan en secondes (pour les commandes planifiées)
        'scan_interval' => env('DETECTION_INTERVAL', 5),

        // Mode démo : génère des attaques aléatoires
        'demo_mode'    => env('DEMO_AUTO_ATTACKS', true),
        'demo_rate'    => (int) env('DEMO_ATTACK_RATE', 30), // % de chance par polling

        // Types d'attaques surveillés
        'monitored_types' => [
            'DDoS', 'SQL Injection', 'XSS', 'Brute Force',
            'Port Scan', 'Ransomware', 'Phishing', 'MITM',
            'Buffer Overflow', 'DNS Spoofing', 'ARP Poisoning', 'Zero Day',
        ],

        // Règles d'auto-blocage
        'auto_block' => [
            'enabled'         => true,
            'threshold_count' => 5,    // Nombre d'attaques depuis une même IP avant blocage auto
            'window_minutes'  => 10,   // Dans quelle fenêtre de temps
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot
    |--------------------------------------------------------------------------
    */
    'honeypot' => [
        'enabled'     => env('HONEYPOT_ENABLED', true),
        'log_all'     => env('HONEYPOT_LOG_ALL', true),
        'alert_email' => env('HONEYPOT_ALERT_EMAIL', null),

        // Chemins des pièges (URLs accessibles)
        'trap_paths' => [
            '/wp-admin'                  => 'fake_wordpress',
            '/phpmyadmin'                => 'fake_phpmyadmin',
            '/admin'                     => 'fake_admin',
            '/api/v1'                    => 'fake_api',
            '/internal/confidential.pdf' => 'canary_token',
        ],

        // IPs toujours ignorées par le honeypot (localhost, etc.)
        'whitelist' => [
            '127.0.0.1',
            '::1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Géolocalisation
    |--------------------------------------------------------------------------
    */
    'geo' => [
        'provider' => env('GEO_PROVIDER', 'local'),  // local | ipgeolocation | ipapi
        'api_key'  => env('GEO_API_KEY', null),
        'cache_ttl' => 3600, // secondes

        // Pays considérés à haut risque
        'high_risk_countries' => [
            'Chine', 'Russie', 'Corée du Nord', 'Iran', 'Syrie',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alarmes
    |--------------------------------------------------------------------------
    */
    'alarms' => [
        'sound_enabled'  => true,
        'speech_enabled' => true,
        'speech_lang'    => 'fr-FR',
        'speech_phrases' => [
            'ALERTE SYSTÈME',
            'ALERTE SYSTÈME',
            'ATTAQUE DÉTECTÉE',
        ],
        'auto_stop_seconds' => 15,

        // Sévérités qui déclenchent alarme + voix
        'trigger_severities' => ['high', 'critical'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'refresh_interval' => 5000,   // ms — polling stats
        'live_interval'    => 3000,   // ms — live attacks feed
        'max_feed_items'   => 50,
        'chart_hours'      => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | APIs Externes (optionnelles)
    |--------------------------------------------------------------------------
    */
    'apis' => [
        'virustotal' => [
            'key'     => env('VIRUSTOTAL_API_KEY'),
            'base_url' => 'https://www.virustotal.com/api/v3/',
        ],
        'shodan' => [
            'key'     => env('SHODAN_API_KEY'),
            'base_url' => 'https://api.shodan.io/',
        ],
        'abuseipdb' => [
            'key'     => env('ABUSEIPDB_API_KEY'),
            'base_url' => 'https://api.abuseipdb.com/api/v2/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (RateLimitMiddleware)
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        // Limites par route (clé = nom de route)
        'otp.send' => [
            'max_attempts' => 3,
            'decay_minutes' => 15,
        ],
        'otp.resend' => [
            'max_attempts' => 3,
            'decay_minutes' => 15,
        ],
        'otp.verify' => [
            'max_attempts' => 5,
            'decay_minutes' => 5,
        ],
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
    ],

];
