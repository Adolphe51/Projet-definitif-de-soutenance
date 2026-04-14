@extends('layouts.app')
@section('title', 'Tableau de Bord — CyberGuard')
@section('page-title', '🛡️ Tableau de Bord')

@section('content')

<!-- Stats principales -->
<div class="stats-grid">
    <div class="stat-card stat-attacks">
        <div class="stat-value" id="stat-total">{{ $stats['total_attacks'] }}</div>
        <div class="stat-label">Total Attaques</div>
        <div class="stat-icon">💀</div>
    </div>

    <div class="stat-card stat-critical">
        <div class="stat-value" id="stat-critical">{{ $stats['critical'] }}</div>
        <div class="stat-label">Critiques</div>
        <div class="stat-icon">🔴</div>
    </div>

    <div class="stat-card stat-blocked">
        <div class="stat-value" id="stat-blocked">{{ $stats['blocked'] }}</div>
        <div class="stat-label">IPs Bloquées</div>
        <div class="stat-icon">🛡️</div>
    </div>

    <div class="stat-card stat-active">
        <div class="stat-value" id="stat-active">{{ $stats['active'] }}</div>
        <div class="stat-label">Actives Now</div>
        <div class="stat-icon">⚡</div>
    </div>
</div>

<!-- Stats secondaires -->
<div class="stats-grid-secondary">
    <div class="stat-card stat-countries">
        <div class="stat-value" id="stat-countries">{{ $stats['countries_count'] }}</div>
        <div class="stat-label">Pays Sources</div>
        <div class="stat-icon">🌍</div>
    </div>

    <div class="stat-card stat-perhour">
        <div class="stat-value" id="stat-perhour">{{ $stats['attacks_per_hour'] }}</div>
        <div class="stat-label">Attaques / Heure</div>
        <div class="stat-icon">⏱️</div>
    </div>

    <div class="stat-card stat-blocked-ips">
        <div class="stat-value" id="stat-blocked-ips">{{ $stats['blocked_ips_count'] }}</div>
        <div class="stat-label">IPs Bloquées</div>
        <div class="stat-icon">🛑</div>
    </div>

    <div class="stat-card stat-honeypots">
        <div class="stat-value" id="stat-active-honeypots">{{ $stats['active_honeypots'] }}</div>
        <div class="stat-label">Honeypots Actifs</div>
        <div class="stat-icon">🎣</div>
    </div>
</div>

<!-- Live feed et graphique attaques -->
<div class="grid-2">
    <!-- Flux en temps réel -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Flux en Temps Réel</div>
            <a href="#" class="btn btn-sm btn-primary">Live</a>
        </div>
        <div id="live-feed">
            @foreach($recentAttacks as $attack)
            <div class="feed-item">
                <span class="feed-icon">{{ $attack->type_icon }}</span>
                <div class="feed-content">
                    <div class="feed-title">
                        <span>{{ $attack->type }}</span>
                        <span class="badge badge-{{ $attack->severity }}">{{ $attack->severity }}</span>
                        @if($attack->is_simulation)
                        <span class="badge badge-simulation">SIM</span>
                        @endif
                    </div>
                    <div class="feed-details">
                        <span class="ip">{{ $attack->source_ip }}</span> → {{ $attack->city }}, {{ $attack->country }}
                    </div>
                </div>
                <div class="feed-time">{{ $attack->created_at->diffForHumans() }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Graphique types d'attaques -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Types d'Attaques</div>
        </div>
        <canvas id="attackChart" height="280"></canvas>
    </div>
</div>

<!-- Alertes et interactions Honeypot -->
<div class="grid-2">
    <!-- Alertes récentes -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Alertes Récentes</div>
            <a href="#" class="btn btn-sm btn-primary">Toutes</a>
        </div>
        <div id="alerts-list">
            @forelse($recentAlerts as $alert)
            <div class="alert-item alert-{{ $alert->severity }}">
                <div class="alert-icon">
                    {{ $alert->severity === 'critical' ? '💀' : ($alert->severity === 'high' ? '🔴' : '⚠️') }}
                </div>
                <div class="alert-content">
                    <div class="alert-title">{{ $alert->title }}</div>
                    <div class="alert-message">{{ $alert->message }}</div>
                    <div class="alert-time">{{ $alert->created_at->diffForHumans() }}</div>
                </div>
                @if(!$alert->acknowledged)
                <span class="alert-unread"></span>
                @endif
            </div>
            @empty
            <div class="no-alerts">Aucune alerte récente</div>
            @endforelse
        </div>
    </div>

    <!-- Interactions Honeypot récentes -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Interactions Honeypot Récentes</div>
        </div>
        <div id="honeypot-interactions">
            @forelse($recentInteractions as $interaction)
            <div class="honeypot-item">
                <div class="honeypot-ip">{{ $interaction->source_ip }}</div>
                <div class="honeypot-trap">{{ $interaction->trap->name ?? 'N/A' }}</div>
                <div class="honeypot-location">{{ $interaction->city }}, {{ $interaction->country }}</div>
                <div class="honeypot-time">{{ $interaction->created_at->diffForHumans() }}</div>
            </div>
            @empty
            <div class="no-interactions">Aucune interaction récente</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
