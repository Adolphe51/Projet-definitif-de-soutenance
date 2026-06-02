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
    | Mode Démo vs Environnement Production
    |--------------------------------------------------------------------------
    
    DOCUMENTATION: À lire avant de comprendre la configuration
    
    **Mode Démo** (local development / presentations) :
    - Variables d'env: DEMO_AUTO_ATTACKS=false, HONEYPOT_DEMO_MODE=false
    - Comportement:
      * Le dashboard affiche les événements déjà collectés
      * Les simulations sont lancées volontairement depuis le laboratoire
      * Le honeypot reste secondaire et non central dans la démonstration
      * GeoService retourne des données locales (base de données)
    - Entrées de données: seed de démonstration, actions métier auditées, simulations manuelles
    - Cas d'usage: présentations, développement, démo orales
    
    **Environnement Production** (APP_ENV=production) :
    - Variables d'env: DEMO_AUTO_ATTACKS=false
    - Comportement:
      * Aucune génération d'attaque aléatoire
      * Dashboard affiche uniquement les données réelles
      * Honeypot ne simule pas, enregistre les attaques réelles
      * Seeder refusé (ou génère minimum de données)
      * GeoService appelle vraies APIs si configuré
    - Entrées de données: événements réels uniquement
    - Audit: tous les changements loggés
    
    **Variables clés à contrôler** :
    - DEMO_AUTO_ATTACKS : conservé pour compatibilité mais désactivé dans le parcours recommandé
    - APP_ENV : démarcation environnement (local/testing/production)
    - GEO_PROVIDER : local (données simulées) vs api réelles
    
    |--------------------------------------------------------------------------
    */

    'mode' => [
        // À quel moment est-on en "mode démo simulé" ?
        'is_demo' => env('DEMO_AUTO_ATTACKS', env('APP_ENV') === 'local'),
        'is_production' => env('APP_ENV') === 'production',
    ],

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
            'debug_code' => [
                // Active l'affichage temporaire du code OTP pour les démos sans vraie messagerie.
                'enabled' => env('CYBERGUARD_AUTH_DEBUG_OTP_ENABLED', env('APP_ENV') === 'local'),
                'toast_duration_ms' => (int) env('CYBERGUARD_AUTH_DEBUG_OTP_TOAST_DURATION_MS', 45000),
            ],
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

        // Mode démo : compatibilité conservée, génération automatique désactivée dans l'application
        'demo_mode'    => env('DEMO_AUTO_ATTACKS', false),
        'demo_rate'    => (int) env('DEMO_ATTACK_RATE', 30), // % de chance par polling

        // Périmètre volontairement resserré pour la soutenance
        'focused_rule_ids' => [
            'brute_force_ssh',
            'http_recon_scan',
            'sql_injection',
            'xss_attempt',
        ],

        // Types d'attaques surveillés
        'monitored_types' => [
            'Brute Force',
            'Port Scan',
            'SQL Injection',
            'XSS',
        ],

        // Détection applicative de reconnaissance HTTP.
        // Utile pour les scans qui atteignent réellement le service web.
        'recon' => [
            'window_minutes' => (int) env('CYBERGUARD_RECON_WINDOW_MINUTES', 3),
            'cooldown_seconds' => (int) env('CYBERGUARD_RECON_COOLDOWN_SECONDS', 120),
            'distinct_paths_threshold' => (int) env('CYBERGUARD_RECON_DISTINCT_PATHS_THRESHOLD', 4),
            'not_found_threshold' => (int) env('CYBERGUARD_RECON_404_THRESHOLD', 3),
            'sensitive_path_threshold' => (int) env('CYBERGUARD_RECON_SENSITIVE_PATH_THRESHOLD', 2),
            'request_threshold' => (int) env('CYBERGUARD_RECON_REQUEST_THRESHOLD', 4),
            'suspicious_user_agents' => [
                'nmap',
                'nse',
                'masscan',
                'zgrab',
                'nikto',
                'sqlmap',
                'python-requests',
                'go-http-client',
                'curl',
                'wget',
            ],
            'sensitive_paths' => [
                '/.env',
                '/.git',
                '/admin',
                '/backup',
                '/cgi-bin',
                '/config',
                '/debug',
                '/login',
                '/manager/html',
                '/phpinfo.php',
                '/phpmyadmin',
                '/server-status',
                '/vendor/phpunit',
                '/wp-admin',
                '/wp-login.php',
            ],
        ],

        // Ingestion de journaux d'acces web (nginx/apache/caddy en format combine/simple).
        // Permet de faire remonter des tests reels qui laissent des traces HTTP.
        'log_ingestion' => [
            'enabled' => env('CYBERGUARD_WEB_LOG_INGESTION_ENABLED', false),
            'access_log_path' => env('CYBERGUARD_WEB_ACCESS_LOG_PATH'),
            'max_lines_per_run' => (int) env('CYBERGUARD_WEB_LOG_MAX_LINES_PER_RUN', 500),
            'offset_ttl_hours' => (int) env('CYBERGUARD_WEB_LOG_OFFSET_TTL_HOURS', 24),
            'cooldown_seconds' => (int) env('CYBERGUARD_WEB_LOG_COOLDOWN_SECONDS', 60),
            'tool_signatures' => [
                'gobuster' => ['gobuster'],
                'nikto' => ['nikto'],
                'ffuf' => ['ffuf', 'fuzz faster u fool'],
                'sqlmap' => ['sqlmap'],
                'burpsuite' => ['burp', 'burp suite'],
                'metasploit' => ['metasploit'],
                'nmap' => ['nmap', 'nse'],
                'dirbuster' => ['dirbuster'],
                'wfuzz' => ['wfuzz'],
                'zgrab' => ['zgrab'],
            ],
            'sensitive_paths' => [
                '/.env',
                '/.git',
                '/admin',
                '/backup',
                '/cgi-bin',
                '/config',
                '/debug',
                '/manager/html',
                '/phpinfo.php',
                '/phpmyadmin',
                '/server-status',
                '/vendor/phpunit',
                '/wp-admin',
                '/wp-login.php',
            ],
            'sql_signatures' => [
                ' union ',
                ' union%20',
                "' or '1'='1",
                'information_schema',
                'sleep(',
                'benchmark(',
                'select ',
                'drop table',
            ],
            'xss_signatures' => [
                '<script',
                '%3cscript',
                'javascript:',
                'onerror=',
                'onload=',
                'alert(',
            ],
            'traversal_signatures' => [
                '../',
                '..%2f',
                '%2e%2e%2f',
                '..\\',
            ],
        ],

        // Règles d'auto-blocage
        'auto_block' => [
            'enabled' => env('CYBERGUARD_AUTO_BLOCK_ENABLED', true),
            'apply_to_simulations' => env('CYBERGUARD_AUTO_BLOCK_SIMULATIONS', false),
            'default_threshold_count' => 5,
            'default_window_minutes' => 10,
            'default_block_minutes' => 60,
            'permanent_severities' => ['critical'],
            'allowlist' => array_values(array_filter(array_map('trim', explode(',', (string) env('CYBERGUARD_AUTO_BLOCK_ALLOWLIST', '127.0.0.1,::1'))))),
            'trusted_admin_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('CYBERGUARD_TRUSTED_ADMIN_IPS', '127.0.0.1,::1'))))),
            'admin_route_prefixes' => [
                'dashboard',
                'attacks',
                'alerts',
                'honeypot',
                'geo',
                'simulations',
                'api/stats',
                'api/live-attacks',
                'api/geo-data',
            ],
            'per_rule' => [
                'brute_force_ssh' => [
                    'threshold_count' => 3,
                    'window_minutes' => 10,
                    'block_minutes' => 30,
                ],
                'http_recon_scan' => [
                    'threshold_count' => 2,
                    'window_minutes' => 10,
                    'block_minutes' => 30,
                ],
                'sql_injection' => [
                    'threshold_count' => 2,
                    'window_minutes' => 15,
                    'block_minutes' => 45,
                ],
                'xss_attempt' => [
                    'threshold_count' => 3,
                    'window_minutes' => 20,
                    'block_minutes' => 30,
                ],
            ],
            'per_type' => [
                'Brute Force' => [
                    'threshold_count' => 3,
                    'window_minutes' => 10,
                    'block_minutes' => 30,
                ],
                'Port Scan' => [
                    'threshold_count' => 2,
                    'window_minutes' => 10,
                    'block_minutes' => 30,
                ],
                'SQL Injection' => [
                    'threshold_count' => 2,
                    'window_minutes' => 15,
                    'block_minutes' => 45,
                ],
                'XSS' => [
                    'threshold_count' => 3,
                    'window_minutes' => 20,
                    'block_minutes' => 30,
                ],
            ],
            'honeypot' => [
                'risk_score_threshold' => 95,
                'block_minutes' => 120,
                'permanent' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mini Site Métier
    |--------------------------------------------------------------------------
    */
    'mini_site' => [
        'visible_features' => [
            'users',
            'services',
            'messages',
        ],
        'refresh_on_seed' => env('CYBERGUARD_MINI_SITE_REFRESH_ON_SEED', env('APP_ENV') !== 'production'),
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
        // Simulation automatique (UI + scheduler). Désactive en prod si besoin.
        'demo_mode'   => env('HONEYPOT_DEMO_MODE', env('DEMO_AUTO_ATTACKS', false)),
        'demo_rate'   => (int) env('HONEYPOT_DEMO_RATE', 20), // % de chance par polling (live-stats)

        // Chemins des pièges (URLs accessibles)
        'trap_paths' => [
            '/phpmyadmin'                => 'fake_phpmyadmin',
            '/admin'                     => 'fake_admin',
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
        'provider' => env('GEO_PROVIDER', 'auto'),  // auto | local | ipgeolocation | ipapi
        'api_key'  => env('GEO_API_KEY', null),
        'cache_ttl' => 3600, // secondes
        'timeout' => 3,

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
