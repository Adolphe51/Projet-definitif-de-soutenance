@extends('layouts.app')
@section('title', 'Tableau de Bord — CyberGuard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Pilotage centré sur le parcours réel : connexion sécurisée, audit des actions, détection d’incidents et analyse SOC.')

@section('content')
    @php
        $healthTone = $stats['critical'] > 0 ? 'critical' : ($stats['active'] > 0 ? 'medium' : 'low');
        $auditLabel = static function ($action) {
            return match (true) {
                $action === 'login_success' => 'Connexion réussie',
                $action === 'login_failed' => 'Connexion refusée',
                $action === 'otp_verified' => 'OTP validé',
                $action === 'otp_failed' => 'OTP refusé',
                str_starts_with($action, 'intranet_') => 'Action mini site',
                str_starts_with($action, 'attack.') => 'Traitement incident',
                str_starts_with($action, 'blocked_ip.') => 'Blocage IP',
                default => 'Événement sécurité',
            };
        };
    @endphp

    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Chaîne de supervision</span>
            <h2>Montrer ce que CyberGuard collecte, comprend et aide réellement à traiter.</h2>
            <p>
                Le parcours recommandé est simple : un utilisateur entre par une connexion sécurisée,
                agit sur l'application métier, CyberGuard journalise, détecte si nécessaire,
                puis l’équipe visualise et traite les événements dans ce dashboard.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('intranet.index') }}" class="btn btn-primary">Ouvrir l'application métier</a>
                <a href="{{ route('alerts.index') }}" class="btn btn-secondary-outline">Consulter les alertes</a>
            </div>
        </div>

        <div class="dashboard-health dashboard-health--{{ $healthTone }}">
            <div class="dashboard-health-label">État actuel</div>
            <div class="dashboard-health-value">
                @if($stats['critical'] > 0)
                    Traitement prioritaire
                @elseif($stats['active'] > 0)
                    Analyse en cours
                @else
                    Supervision stable
                @endif
            </div>
            <div class="dashboard-health-meta">
                {{ $stats['critical'] }} critique(s) non bloquée(s) · {{ $stats['unread_alerts'] }} alerte(s) non lue(s)
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong id="stat-perhour">{{ $stats['attacks_per_hour'] }}</strong>
                    <span>événements / heure</span>
                </div>
                <div>
                    <strong id="stat-top-type">{{ $stats['top_attack_type'] }}</strong>
                    <span>type dominant</span>
                </div>
            </div>
        </div>
    </section>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" id="stat-total">{{ $stats['total_attacks'] }}</div>
            <div class="stat-label">Incidents observés</div>
            <div class="stat-icon">01</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-critical">{{ $stats['critical'] }}</div>
            <div class="stat-label">Critiques à traiter</div>
            <div class="stat-icon">02</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-blocked">{{ $stats['blocked'] }}</div>
            <div class="stat-label">IPs bloquées</div>
            <div class="stat-icon">03</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-active">{{ $stats['active'] }}</div>
            <div class="stat-label">En investigation</div>
            <div class="stat-icon">04</div>
        </div>
    </div>

    <div class="stats-grid-secondary">
        <div class="stat-card">
            <div class="stat-value" id="stat-unread-alerts">{{ $stats['unread_alerts'] }}</div>
            <div class="stat-label">Alertes non lues</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-auth-audit">{{ $stats['auth_audit_events'] }}</div>
            <div class="stat-label">Audits auth 24h</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-intranet-audit">{{ $stats['intranet_audit_events'] }}</div>
            <div class="stat-label">Actions application 24h</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-manual-sims">{{ $stats['manual_simulation_attacks'] }}</div>
            <div class="stat-label">Attaques simulées</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-countries">{{ $stats['countries_count'] }}</div>
            <div class="stat-label">Pays observés</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-blocked-ips">{{ $stats['blocked_ips_count'] }}</div>
            <div class="stat-label">Blocages actifs</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="stack-md">
            <div class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Incidents récents</div>
                        <p class="section-intro">Derniers événements détectés, simulés manuellement ou remontés depuis le périmètre applicatif.</p>
                    </div>
                    <a href="{{ route('attacks.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div id="live-feed">
                    @forelse($recentAttacks as $attack)
                        <a href="{{ route('attacks.show', $attack->id) }}" class="feed-item dashboard-link-card">
                            <span class="feed-icon">{{ $attack->type_icon }}</span>
                            <div class="feed-content">
                                <div class="feed-title">
                                    <span>{{ $attack->type }}</span>
                                    <span class="badge badge-{{ $attack->severity }}">{{ strtoupper($attack->severity) }}</span>
                                    <span class="badge badge-{{ $attack->status }}">{{ strtoupper($attack->status) }}</span>
                                    @if($attack->is_simulation)
                                        <span class="badge badge-simulation">SIMULATION</span>
                                    @endif
                                </div>
                                <div class="feed-details">
                                    {{ $attack->source_ip }} → {{ $attack->target_ip }}
                                    @if($attack->city || $attack->country)
                                        · {{ $attack->city ?: 'Ville inconnue' }}, {{ $attack->country ?: 'Pays inconnu' }}
                                    @endif
                                </div>
                            </div>
                            <div class="feed-time">{{ $attack->created_at->diffForHumans() }}</div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">🛡️</div>
                            <p class="empty-state-title">Aucun incident récent</p>
                            <p class="empty-state-text">Lance une simulation manuelle ou réalise une action détectable pour alimenter la démonstration.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="grid-2 dashboard-panel-grid">
                <div class="card dashboard-panel">
                    <div class="section-header">
                        <div class="section-title">Répartition des attaques</div>
                    </div>
                    <canvas id="attackChart" height="240" data-attack-data='@json($stats["attack_chart"])'></canvas>
                </div>

                <div class="card dashboard-panel">
                    <div class="section-header">
                        <div class="section-title">Top sources</div>
                    </div>
                    <div class="summary-columns summary-columns--single">
                        <div>
                            <div class="summary-label">Types dominants</div>
                            @forelse($stats['top_attack_types'] as $item)
                                <div class="summary-row">
                                    <span>{{ $item['label'] }}</span>
                                    <strong>{{ $item['count'] }}</strong>
                                </div>
                            @empty
                                <div class="summary-empty">Aucune donnée</div>
                            @endforelse
                        </div>
                        <div>
                            <div class="summary-label">IPs fréquentes</div>
                            @forelse($stats['top_source_ips'] as $item)
                                <div class="summary-row">
                                    <span>{{ $item['label'] }}</span>
                                    <strong>{{ $item['count'] }}</strong>
                                </div>
                            @empty
                                <div class="summary-empty">Aucune donnée</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stack-md">
            <div class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Accès rapides</div>
                        <p class="section-intro">Raccourcis vers les écrans utiles au récit de soutenance.</p>
                    </div>
                </div>
                <div class="quick-links-grid">
                    <a href="{{ route('intranet.index') }}" class="quick-link-card">
                        <strong>Application métier</strong>
                        <span>Connexion sécurisée, actions métier, contenu libre et audit.</span>
                    </a>
                    <a href="{{ route('alerts.index') }}" class="quick-link-card">
                        <strong>Centre d’alertes</strong>
                        <span>Priorisation, acquittement et lecture des signaux critiques.</span>
                    </a>
                    <a href="{{ route('attacks.index') }}" class="quick-link-card">
                        <strong>Incidents</strong>
                        <span>Liste complète des attaques détectées, simulées et traitées.</span>
                    </a>
                    <a href="{{ route('geo.attackers') }}" class="quick-link-card">
                        <strong>Analyse géographique</strong>
                        <span>Origine réseau des événements observés sur le périmètre.</span>
                    </a>
                    @if(auth()->user()?->hasRole('admin'))
                        <a href="{{ route('simulations.index') }}" class="quick-link-card">
                            <strong>Simulations manuelles</strong>
                            <span>Lancer un scénario volontaire et voir les alertes correspondantes.</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Alertes récentes</div>
                        <p class="section-intro">Alertes contextualisées avec leur origine et leur nature.</p>
                    </div>
                    <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-secondary-outline">Ouvrir</a>
                </div>
                <div id="alerts-list">
                    @forelse($recentAlerts as $alert)
                        @php
                            $originLabel = match (true) {
                                $alert->type === 'simulation' => 'Simulation manuelle',
                                $alert->type === 'honeypot' => 'Honeypot secondaire',
                                $alert->type === 'attack' && $alert->attack?->is_simulation => 'Attaque simulée',
                                $alert->type === 'attack' => 'Détection sécurité',
                                $alert->type === 'system' => 'Traitement SOC',
                                default => 'Événement sécurité',
                            };
                        @endphp
                        <div class="alert-item alert-{{ $alert->severity }}">
                            <div class="alert-content">
                                <div class="feed-title">
                                    <span>{{ $alert->title }}</span>
                                    <span class="badge badge-{{ $alert->severity }}">{{ strtoupper($alert->severity) }}</span>
                                </div>
                                <div class="alert-message">{{ $alert->message }}</div>
                                <div class="feed-details">
                                    {{ $originLabel }}
                                    @if($alert->attack)
                                        · {{ $alert->attack->type }} · {{ $alert->attack->source_ip }}
                                    @endif
                                </div>
                            </div>
                            <div class="alert-time">{{ $alert->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">🔕</div>
                            <p class="empty-state-title">Aucune alerte récente</p>
                            <p class="empty-state-text">Le centre d’alertes affichera ici les signaux issus du mini site, du réseau ou des simulations manuelles.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Journal d’audit récent</div>
                        <p class="section-intro">Trace visible des connexions et actions métier significatives.</p>
                    </div>
                </div>
                <div class="quick-links-grid">
                    @forelse($recentAuditTrail as $audit)
                        @php
                            $badge = match ($audit->importance) {
                                'critique' => 'critical',
                                'elevee' => 'high',
                                'moyenne' => 'warning',
                                default => 'success',
                            };
                        @endphp
                        <div class="quick-link-card">
                            <strong>{{ $auditLabel($audit->action) }}</strong>
                            <span>
                                {{ $audit->actor?->nom ?? $audit->actor?->email ?? 'Système' }}
                                @if($audit->ip_address)
                                    · {{ $audit->ip_address }}
                                @endif
                            </span>
                            <div class="feed-title">
                                <span class="badge badge-{{ $badge }}">{{ strtoupper($audit->importance) }}</span>
                                <span class="mono text-muted-small">{{ $audit->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">📘</div>
                            <p class="empty-state-title">Aucun audit récent</p>
                            <p class="empty-state-text">Les connexions et actions du mini site apparaîtront ici au fil de la démonstration.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
