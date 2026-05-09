@extends('layouts.app')
@section('title', 'Honeypot')
@section('page-title', 'Environnement honeypot')
@section('page-subtitle', 'Zone dédiée aux pièges de démonstration, aux interactions capturées et au suivi du comportement des attaquants.')

@section('content')
    @php
        $activeTraps = $traps->where('status', 'active')->count();
        $triggeredTraps = $traps->where('status', 'triggered')->count();
        $trapIcons = [
            'fake_login' => '🔐',
            'fake_admin' => '⚙️',
            'fake_db' => '🗄️',
            'fake_api' => '🔌',
            'fake_ssh' => '💻',
            'fake_ftp' => '📁',
            'fake_phpmyadmin' => '🐬',
            'fake_wordpress' => '📝',
            'canary_token' => '🐤',
            'fake_document' => '📄',
        ];
        $trapColors = [
            'fake_login' => 'rgba(37, 99, 235, 0.12)',
            'fake_admin' => 'rgba(234, 88, 12, 0.12)',
            'fake_db' => 'rgba(168, 85, 247, 0.12)',
            'fake_api' => 'rgba(22, 163, 74, 0.12)',
            'fake_ssh' => 'rgba(245, 158, 11, 0.12)',
            'fake_ftp' => 'rgba(59, 130, 246, 0.12)',
            'fake_phpmyadmin' => 'rgba(236, 72, 153, 0.12)',
            'fake_wordpress' => 'rgba(249, 115, 22, 0.12)',
            'canary_token' => 'rgba(245, 158, 11, 0.12)',
            'fake_document' => 'rgba(37, 99, 235, 0.12)',
        ];
    @endphp

    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Pièges actifs</span>
            <h2>Un honeypot plus clair pour montrer comment CyberGuard attire, observe et trace les attaquants.</h2>
            <p>
                Cette zone reste distincte du dashboard principal pour bien séparer la démonstration des pièges,
                la supervision des interactions et le suivi des preuves capturées.
            </p>
            <div class="dashboard-actions">
                <button class="btn btn-warning" onclick="initializeTraps()">Initialiser les pièges</button>
                <button class="btn btn-primary" onclick="simulateAll()">Simuler une attaque</button>
            </div>
        </div>

        <div class="dashboard-health {{ $triggeredTraps > 0 ? 'dashboard-health--critical' : 'dashboard-health--low' }}">
            <div class="dashboard-health-label">État du honeypot</div>
            <div class="dashboard-health-value">{{ $triggeredTraps > 0 ? 'Activité suspecte observée' : 'Surveillance active' }}</div>
            <div class="dashboard-health-meta">
                {{ $activeTraps }} piège(s) actif(s) · {{ $triggeredTraps }} déclenché(s) · {{ $totalInteractions }} interaction(s) enregistrée(s)
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong>{{ $uniqueAttackers }}</strong>
                    <span>sources uniques</span>
                </div>
                <div>
                    <strong>{{ $credsCaptured }}</strong>
                    <span>identifiants capturés</span>
                </div>
            </div>
        </div>
    </section>

    <section class="attacks-overview-grid">
        <article class="attacks-overview-card attacks-overview-card--neutral">
            <span class="attacks-overview-label">Pièges</span>
            <strong>{{ $traps->count() }}</strong>
            <p>Nombre total de surfaces honeypot configurées.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--medium">
            <span class="attacks-overview-label">Actifs</span>
            <strong>{{ $activeTraps }}</strong>
            <p>Pièges actuellement opérationnels et visibles pour l’attaquant.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--high">
            <span class="attacks-overview-label">Déclenchés</span>
            <strong>{{ $triggeredTraps }}</strong>
            <p>Pièges récemment touchés par une interaction suspecte.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--critical">
            <span class="attacks-overview-label">Credentials</span>
            <strong>{{ $credsCaptured }}</strong>
            <p>Tentatives d’identifiants capturées pour la démonstration.</p>
        </article>
    </section>

    <div class="hp-layout-grid">
        <div class="hp-stack">
            <section class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Pièges déployés</div>
                        <p class="section-intro">Chaque piège présente son état, ses métadonnées et les actions de démonstration disponibles.</p>
                    </div>
                    <div class="hp-traps-header-note">{{ $traps->count() }} piège(s)</div>
                </div>

                <div class="stack-md">
                    @forelse($traps as $trap)
                        <article class="hp-trap-card {{ $trap->status }}" id="trap-{{ $trap->id }}">
                            <div class="hp-trap-head">
                                <div class="hp-trap-icon" style="background: {{ $trapColors[$trap->type] ?? 'rgba(37, 99, 235, 0.12)' }};">
                                    {{ $trapIcons[$trap->type] ?? '🎣' }}
                                </div>

                                <div class="hp-trap-body">
                                    <div class="hp-trap-title-row">
                                        <div class="hp-trap-name">{{ $trap->name }}</div>
                                        <span class="hp-status-badge {{ $trap->status }}">
                                            @if($trap->status === 'active')
                                                ● ACTIF
                                            @elseif($trap->status === 'triggered')
                                                ⚡ DÉCLENCHÉ
                                            @else
                                                ○ INACTIF
                                            @endif
                                        </span>
                                    </div>

                                    <div class="hp-trap-desc">{{ $trap->description }}</div>

                                    <div class="hp-trap-meta-row">
                                        @if($trap->fake_service)
                                            <span class="badge badge-info">{{ $trap->fake_service }}</span>
                                        @endif
                                        @if($trap->port)
                                            <span class="badge badge-primary">Port {{ $trap->port }}</span>
                                        @endif
                                        @if($trap->path)
                                            <span class="mono text-muted-small">{{ $trap->path }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="hp-trap-stats">
                                <div class="hp-stat-card">
                                    <span>Interactions</span>
                                    <strong style="color: {{ $trap->interactions_count > 0 ? 'var(--accent-red)' : 'var(--text-primary)' }};">
                                        {{ $trap->interactions_count }}
                                    </strong>
                                </div>
                                <div class="hp-stat-card">
                                    <span>Dernière activité</span>
                                    <strong>{{ $trap->last_triggered_at ? $trap->last_triggered_at->diffForHumans() : 'Aucune' }}</strong>
                                </div>
                            </div>

                            <div class="hp-trap-actions">
                                @php
                                    $trapPreviewRoute = match ($trap->type) {
                                        'fake_admin' => route('honeypot.trap.admin'),
                                        'fake_phpmyadmin' => route('honeypot.trap.pma'),
                                        default => null,
                                    };
                                @endphp
                                <button class="btn btn-warning btn-sm" onclick='simulateTrap({{ $trap->id }}, @json($trap->name))'>
                                    Simuler
                                </button>
                                <a href="{{ route('honeypot.detail', $trap->id) }}" class="btn btn-primary btn-sm">
                                    Détails
                                </a>
                                @if($trap->path && $trapPreviewRoute)
                                    <a href="{{ $trapPreviewRoute }}" target="_blank" class="btn btn-secondary-outline btn-sm">
                                        Voir le piège
                                    </a>
                                @endif
                                <button
                                    class="btn btn-sm {{ $trap->status === 'active' ? 'btn-danger' : 'btn-success' }}"
                                    onclick="toggleTrap({{ $trap->id }}, this)"
                                    style="margin-left:auto;"
                                >
                                    {{ $trap->status === 'active' ? 'Mettre en pause' : 'Activer' }}
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">🍯</div>
                            <p class="empty-state-title">Aucun piège configuré</p>
                            <p class="empty-state-text">Initialise les pièges pour préparer la démonstration honeypot.</p>
                            <div class="dashboard-actions">
                                <button class="btn btn-warning" onclick="initializeTraps()">Initialiser les pièges</button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="hp-side-stack">
            <section class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Interactions récentes</div>
                        <p class="section-intro">Lecture rapide des dernières visites, des scores de risque et des traces capturées.</p>
                    </div>
                    <span class="live-indicator-dot"></span>
                </div>

                <div class="hp-interaction-feed" id="interaction-feed">
                    @forelse($interactions as $interaction)
                        @php
                            $isHigh = $interaction->risk_score >= 85;
                            $riskColor = $interaction->risk_score >= 85 ? 'var(--accent-red)' : ($interaction->risk_score >= 60 ? 'var(--accent-yellow)' : 'var(--accent-green)');
                        @endphp
                        <article class="hp-interaction-card {{ $isHigh ? 'high-risk' : 'med-risk' }}">
                            <div class="hp-interaction-head">
                                <div>
                                    <div class="hp-trap-name">{{ $interaction->trap->name ?? 'Piège inconnu' }}</div>
                                    <div class="hp-interaction-ip">{{ $interaction->source_ip }}</div>
                                    <div class="hp-interaction-location">{{ $interaction->city }}, {{ $interaction->country }}</div>
                                </div>

                                <div class="hp-risk-box">
                                    <div class="hp-risk-score" style="color: {{ $riskColor }};">{{ $interaction->risk_score }}</div>
                                    <div class="hp-risk-label">Risque</div>
                                    <div class="hp-interaction-time">{{ $interaction->created_at->diffForHumans() }}</div>
                                </div>
                            </div>

                            @if($interaction->credentials_attempted)
                                <div class="hp-creds-box">
                                    <span>👤</span>
                                    <span class="hp-creds-user">{{ $interaction->credentials_attempted['username'] ?? '?' }}</span>
                                    <span class="text-muted-small">:</span>
                                    <span class="hp-creds-pass">{{ $interaction->credentials_attempted['password'] ?? '?' }}</span>
                                </div>
                            @endif

                            <div class="hp-risk-bar">
                                <div class="hp-risk-fill" style="width: {{ $interaction->risk_score }}%; background: {{ $riskColor }};"></div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">🕸️</div>
                            <p class="empty-state-title">Aucun intrus observé</p>
                            <p class="empty-state-text">Les interactions honeypot apparaîtront ici dès qu’un piège sera touché.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="hp-terminal-shell">
                <div class="hp-terminal-head">
                    <div class="section-title">Terminal honeypot</div>
                    <div class="hp-terminal-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <div class="hp-terminal" id="hp-terminal">
                    <div class="t-cyan">honeypot@cyberguard:~$ <span class="t-green">service honeypot status</span></div>
                    <div class="t-green">● honeypot.service - CyberGuard Honeypot Engine</div>
                    <div class="t-gray">   Active: active (running) since startup</div>
                    <div class="t-green">   Traps deployed: {{ $activeTraps }}</div>
                    <div class="t-cyan">honeypot@cyberguard:~$ <span class="t-yellow">tail -f /var/log/honeypot.log</span></div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lastInteractionId = {{ $interactions->first()?->id ?? 0 }};
const terminal = document.getElementById('hp-terminal');
let honeypotAudioContext = null;

function getHoneypotAudioContext() {
    if (!honeypotAudioContext) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
            return null;
        }

        honeypotAudioContext = new AudioContextClass();
    }

    if (honeypotAudioContext.state === 'suspended') {
        honeypotAudioContext.resume();
    }

    return honeypotAudioContext;
}

function playHoneypotTone(frequency, duration, volume) {
    const context = getHoneypotAudioContext();

    if (!context) {
        return;
    }

    const oscillator = context.createOscillator();
    const gain = context.createGain();

    oscillator.type = 'square';
    oscillator.frequency.value = frequency;
    gain.gain.value = volume;

    oscillator.connect(gain);
    gain.connect(context.destination);

    oscillator.start();
    oscillator.stop(context.currentTime + duration);
}

function triggerHoneypotAlarm(level = 'medium') {
    const base = level === 'high' ? 860 : 620;
    playHoneypotTone(base, 0.12, 0.035);
    window.setTimeout(() => playHoneypotTone(base * 1.15, 0.12, 0.03), 200);
}

function addTerminalLine(text, cssClass = 't-green') {
    const timestamp = new Date().toLocaleTimeString('fr-FR', { hour12: false });
    const line = document.createElement('div');

    line.className = cssClass;
    line.textContent = `[${timestamp}] ${text}`;
    terminal.appendChild(line);
    terminal.scrollTop = terminal.scrollHeight;

    while (terminal.children.length > 50) {
        terminal.removeChild(terminal.firstChild);
    }
}

async function simulateTrap(id, name) {
    addTerminalLine(`Simulation déclenchée sur: ${name}`, 't-yellow');

    try {
        const response = await csrfFetch(`/honeypot/simulate/${id}`, { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            const interaction = data.interaction;
            addTerminalLine(`INTRUS DÉTECTÉ: ${interaction.ip} (${interaction.city}, ${interaction.country}) — Score: ${interaction.risk_score}/100`, 't-red');

            if (interaction.credentials) {
                addTerminalLine(`CREDENTIALS: user=${interaction.credentials.username} pass=${interaction.credentials.password}`, 't-red');
            }

            if (interaction.actions?.length) {
                addTerminalLine(`ACTIONS: ${interaction.actions.join(' → ')}`, 't-yellow');
            }

            showToast(`Piège ${name} déclenché par ${interaction.ip}.`, interaction.risk_score >= 85 ? 'error' : 'warning');

            if (interaction.risk_score >= 85) {
                triggerHoneypotAlarm('high');
            }

            loadLiveStats();
        }
    } catch (error) {
        showToast('La simulation honeypot a échoué.', 'error');
    }
}

async function simulateAll() {
    const traps = document.querySelectorAll('.hp-trap-card.active');
    addTerminalLine(`Simulation globale sur ${traps.length} pièges actifs`, 't-cyan');

    if (traps.length === 0) {
        showToast('Aucun piège actif. Initialise ou réactive un piège.', 'warning');
        return;
    }

    const selectedCard = traps[Math.floor(Math.random() * traps.length)];
    const id = selectedCard.id.replace('trap-', '');
    const name = selectedCard.querySelector('.hp-trap-name')?.textContent;

    if (id && name) {
        simulateTrap(parseInt(id, 10), name);
    }
}

async function initializeTraps() {
    addTerminalLine('Initialisation des pièges...', 't-cyan');

    try {
        const response = await csrfFetch('/honeypot/initialize', { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            addTerminalLine('Tous les pièges déployés avec succès', 't-green');
            showToast('Pièges honeypot déployés.', 'success');
            window.setTimeout(() => window.location.reload(), 1200);
        }
    } catch (error) {
        showToast('Impossible d’initialiser les pièges.', 'error');
    }
}

async function toggleTrap(id, btn) {
    try {
        const response = await csrfFetch(`/honeypot/toggle/${id}`, { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            const card = document.getElementById(`trap-${id}`);
            card.classList.remove('active', 'inactive', 'triggered');
            card.classList.add(data.status);

            const statusBadge = card.querySelector('.hp-status-badge');

            if (statusBadge) {
                statusBadge.className = `hp-status-badge ${data.status}`;
                statusBadge.textContent = data.status === 'active' ? '● ACTIF' : '○ INACTIF';
            }

            btn.className = `btn btn-sm ${data.status === 'active' ? 'btn-danger' : 'btn-success'}`;
            btn.textContent = data.status === 'active' ? 'Mettre en pause' : 'Activer';
            addTerminalLine(`Piège #${id} ${data.status === 'active' ? 'activé' : 'désactivé'}`, data.status === 'active' ? 't-green' : 't-yellow');
        }
    } catch (error) {
        showToast('Le changement d’état du piège a échoué.', 'error');
    }
}

async function loadLiveStats() {
    try {
        const response = await fetch('/honeypot/live-stats');
        const data = await response.json();

        document.getElementById('hp-total').textContent = data.total;
        document.getElementById('hp-unique').textContent = data.unique_ips;
        document.getElementById('hp-creds').textContent = data.creds;

        if (data.interactions.length > 0) {
            const latest = data.interactions[0];

            if (latest.id > lastInteractionId) {
                lastInteractionId = latest.id;
                prependInteraction(latest);
                addTerminalLine(`NOUVEL INTRUS: ${latest.ip} → ${latest.trap_name}`, 't-red');
            }
        }
    } catch (error) {}
}

function prependInteraction(interaction) {
    const feed = document.getElementById('interaction-feed');
    const isHigh = interaction.risk_score >= 85;
    const riskColor = isHigh ? 'var(--accent-red)' : 'var(--accent-yellow)';
    const item = document.createElement('article');

    item.className = `hp-interaction-card ${isHigh ? 'high-risk' : 'med-risk'}`;
    item.innerHTML = `
        <div class="hp-interaction-head">
            <div>
                <div class="hp-trap-name">${interaction.trap_name}</div>
                <div class="hp-interaction-ip">${interaction.ip}</div>
                <div class="hp-interaction-location">${interaction.city}, ${interaction.country}</div>
            </div>
            <div class="hp-risk-box">
                <div class="hp-risk-score" style="color: ${riskColor};">${interaction.risk_score}</div>
                <div class="hp-risk-label">Risque</div>
                <div class="hp-interaction-time">À l’instant</div>
            </div>
        </div>
        ${interaction.credentials ? `<div class="hp-creds-box"><span>👤</span><span class="hp-creds-user">${interaction.credentials.username}</span><span class="text-muted-small">:</span><span class="hp-creds-pass">${interaction.credentials.password}</span></div>` : ''}
        <div class="hp-risk-bar"><div class="hp-risk-fill" style="width: ${interaction.risk_score}%; background: ${riskColor};"></div></div>
    `;

    if (feed.querySelector('.empty-state')) {
        feed.innerHTML = '';
    }

    feed.insertBefore(item, feed.firstChild);

    if (feed.children.length > 15) {
        feed.removeChild(feed.lastChild);
    }
}

window.setInterval(loadLiveStats, 7000);

window.setInterval(() => {
    if (Math.random() < 0.3) {
        const messages = [
            'Surveillance réseau active...',
            'Analyse des paquets entrants...',
            'Aucune activité suspecte détectée',
            'Vérification intégrité des pièges...',
        ];

        addTerminalLine(messages[Math.floor(Math.random() * messages.length)], 't-gray');
    }
}, 5000);
</script>
@endpush
