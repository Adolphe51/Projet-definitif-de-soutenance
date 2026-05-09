@extends('layouts.app')
@section('title', 'Détection live')
@section('page-title', 'Centre live')
@section('page-subtitle', 'Vue temps réel des attaques détectées avec filtrage rapide, compteurs instantanés et action immédiate sur le dernier attaquant.')

@section('content')
    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Surveillance en direct</span>
            <h2>Une lecture instantanée du flux d’attaques, sans repasser par le dashboard.</h2>
            <p>
                Cette page sert à suivre la détection en continu, filtrer les niveaux de gravité
                et ouvrir rapidement la fiche d’un incident dès qu’un événement ressort du lot.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('attacks.index') }}" class="btn btn-primary">Retour aux incidents</a>
                <a href="{{ route('geo.attackers') }}" class="btn btn-secondary-outline">Vue géographique</a>
            </div>
        </div>

        <div class="dashboard-health dashboard-health--medium">
            <div class="dashboard-health-label">Flux live</div>
            <div class="dashboard-health-value">Mise à jour toutes les 3 secondes</div>
            <div class="dashboard-health-meta">
                Le flux se met à jour automatiquement et met en avant le dernier événement détecté.
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong id="geo-total">0</strong>
                    <span>événements visibles</span>
                </div>
                <div>
                    <strong id="c-packets">0</strong>
                    <span>paquets / cycle</span>
                </div>
            </div>
        </div>
    </section>

    <section class="attacks-overview-grid">
        <article class="attacks-overview-card attacks-overview-card--neutral">
            <span class="attacks-overview-label">Total</span>
            <strong id="c-total">0</strong>
            <p>Volume global retourné par l’API live.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--critical">
            <span class="attacks-overview-label">Critiques</span>
            <strong id="c-critical">0</strong>
            <p>Attaques nécessitant la plus forte vigilance.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--high">
            <span class="attacks-overview-label">Élevées</span>
            <strong id="c-high">0</strong>
            <p>Incidents qui peuvent rapidement monter en pression.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--medium">
            <span class="attacks-overview-label">État</span>
            <strong id="attack-counter-display">0</strong>
            <p>Compteur synthétique du flux reçu.</p>
        </article>
    </section>

    <section class="card dashboard-panel">
        <div class="live-indicator-bar">
            <span class="live-indicator-dot"></span>
            <span>Surveillance en direct activée</span>
            <span class="live-indicator-note">Le flux est rafraîchi automatiquement toutes les 3 secondes.</span>
        </div>
    </section>

    <div class="live-layout-grid">
        <section class="card dashboard-panel live-stream-shell">
            <div class="section-header">
                <div>
                    <div class="section-title">Flux d’attaques</div>
                    <p class="section-intro">Filtre le niveau de sévérité puis ouvre la fiche d’un incident en un clic.</p>
                </div>
            </div>

            <div class="live-toolbar">
                <div class="live-filter-bar">
                    <button class="live-filter-btn active" onclick="filterSeverity('all', this)">Toutes</button>
                    <button class="live-filter-btn" onclick="filterSeverity('critical', this)">Critiques</button>
                    <button class="live-filter-btn" onclick="filterSeverity('high', this)">Élevées</button>
                    <button class="live-filter-btn" onclick="filterSeverity('medium', this)">Moyennes</button>
                    <button class="live-filter-btn" onclick="filterSeverity('low', this)">Faibles</button>
                </div>
            </div>

            <div class="live-stream" id="attack-stream">
                <div class="empty-state">
                    <div class="empty-state-icon">📡</div>
                    <p class="empty-state-title">En attente d’attaques</p>
                    <p class="empty-state-text">Les nouvelles détections apparaîtront ici dès le prochain cycle live.</p>
                </div>
            </div>
        </section>

        <div class="live-side-stack">
            <section class="live-radar-shell">
                <div class="section-title section-title--spaced">Radar live</div>
                <div class="live-radar-box">
                    <div class="live-radar">
                        <div class="live-radar-sweep"></div>
                        <div id="radar-dots"></div>
                        <div class="live-radar-label">RADAR</div>
                    </div>
                </div>
            </section>

            <section class="live-counter-shell">
                <div class="section-title section-title--spaced">Dernier attaquant</div>
                <div id="last-attack-content" class="summary-empty">Aucune attaque reçue pour le moment.</div>
                <div class="dashboard-actions" style="margin-top: 1rem;">
                    <button class="btn btn-danger btn-center" style="width: 100%;" onclick="blockLastAttacker()">
                        Bloquer le dernier attaquant
                    </button>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let attackCount = 0;
let critCount = 0;
let highCount = 0;
let lastAttackId = null;
let currentFilter = 'all';
let allAttacks = [];
let liveAudioContext = null;

function getLiveAudioContext() {
    if (!liveAudioContext) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
            return null;
        }

        liveAudioContext = new AudioContextClass();
    }

    if (liveAudioContext.state === 'suspended') {
        liveAudioContext.resume();
    }

    return liveAudioContext;
}

function playLiveTone(frequency, duration, volume) {
    const context = getLiveAudioContext();

    if (!context) {
        return;
    }

    const oscillator = context.createOscillator();
    const gain = context.createGain();

    oscillator.type = 'sawtooth';
    oscillator.frequency.value = frequency;
    gain.gain.value = volume;

    oscillator.connect(gain);
    gain.connect(context.destination);

    oscillator.start();
    oscillator.stop(context.currentTime + duration);
}

function triggerLiveAlarm(severity) {
    const base = severity === 'critical' ? 920 : 700;
    playLiveTone(base, 0.12, 0.035);
    window.setTimeout(() => playLiveTone(base * 1.12, 0.12, 0.03), 180);
}

async function fetchLiveAttacks() {
    try {
        const response = await fetch('/api/live-attacks');
        const data = await response.json();

        attackCount = data.total || 0;
        critCount = data.critical || 0;
        highCount = (data.attacks || []).filter(attack => attack.severity === 'high').length;

        document.getElementById('attack-counter-display').textContent = attackCount.toLocaleString();
        document.getElementById('c-total').textContent = attackCount.toLocaleString();
        document.getElementById('c-critical').textContent = critCount.toLocaleString();
        document.getElementById('c-high').textContent = highCount.toLocaleString();
        document.getElementById('geo-total').textContent = (data.attacks || []).length.toLocaleString();
        document.getElementById('c-packets').textContent = (Math.floor(Math.random() * 5000) + 500).toLocaleString();

        allAttacks = data.attacks || [];
        renderAttacks(allAttacks);
        updateRadar(allAttacks.slice(0, 8));

        if (data.new_attack) {
            const attack = data.new_attack;
            if (attack.alarm && ['critical', 'high'].includes(attack.severity)) {
                triggerLiveAlarm(attack.severity);
                showToast(`${attack.type} détectée depuis ${attack.ip}.`, attack.severity === 'critical' ? 'error' : 'warning');
            } else {
                showToast(`${attack.type} détectée depuis ${attack.ip}.`, 'info', 3000);
            }
        }
    } catch (error) {
        console.error(error);
    }
}

function renderAttacks(attacks) {
    const filtered = currentFilter === 'all' ? attacks : attacks.filter(attack => attack.severity === currentFilter);
    const stream = document.getElementById('attack-stream');

    if (!filtered.length) {
        stream.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">🛰️</div>
                <p class="empty-state-title">Aucune attaque pour ce filtre</p>
                <p class="empty-state-text">Change le niveau de sévérité ou attends le prochain cycle live.</p>
            </div>
        `;
        document.getElementById('last-attack-content').textContent = 'Aucune attaque reçue pour le moment.';
        return;
    }

    stream.innerHTML = filtered.map(attack => `
        <div class="live-attack-row ${attack.severity}" onclick="viewAttack(${attack.id})">
            <div class="live-attack-icon">${attack.icon}</div>
            <div class="live-attack-info">
                <div class="live-attack-title">
                    <span>${attack.type}</span>
                    <span class="badge badge-${attack.severity}">${attack.severity.toUpperCase()}</span>
                    ${attack.is_simulation ? '<span class="badge badge-simulation">SIM</span>' : ''}
                    ${attack.status === 'blocked' ? '<span class="badge badge-success">BLOQUÉE</span>' : ''}
                </div>
                <div class="live-attack-meta">
                    <span class="ip-addr">${attack.source_ip}</span>
                    <span>${attack.city}, ${attack.country}</span>
                    <span>${attack.target_ip}</span>
                    <span>${attack.time}</span>
                </div>
                <div class="live-attack-desc">${attack.description || ''}</div>
            </div>
            <div class="live-attack-stats">
                <div class="live-attack-packets">${Number(attack.packet_count || 0).toLocaleString()}</div>
                <div class="live-attack-bw">${attack.bandwidth_mbps || 0} Mbps</div>
                <div class="dashboard-actions" style="margin-top: 0.45rem; justify-content: flex-end;">
                    <button class="btn btn-danger btn-sm" onclick="event.stopPropagation(); blockAttack(${attack.id})">Bloquer</button>
                </div>
            </div>
        </div>
    `).join('');

    const last = filtered[0];
    if (last) {
        lastAttackId = last.id;
        document.getElementById('last-attack-content').innerHTML = `
            <div class="attack-title" style="font-size: 1rem; margin-bottom: 0.5rem;">${last.icon} ${last.type}</div>
            <div class="ip-addr" style="margin-bottom: 0.35rem;">${last.source_ip}</div>
            <div class="text-muted-small" style="margin-bottom: 0.35rem;">${last.city}, ${last.country}</div>
            <span class="badge badge-${last.severity}">${last.severity.toUpperCase()}</span>
        `;
    }
}

function updateRadar(attacks) {
    const container = document.getElementById('radar-dots');
    container.innerHTML = attacks.map((attack, index) => {
        const angle = (index / Math.max(attacks.length, 1)) * 360;
        const radius = 30 + Math.random() * 35;
        const x = 80 + radius * Math.cos((angle * Math.PI) / 180);
        const y = 80 + radius * Math.sin((angle * Math.PI) / 180);
        const color = { critical: '#dc2626', high: '#ea580c', medium: '#ca8a04', low: '#16a34a' }[attack.severity] || '#2563eb';
        return `<div class="radar-dot" style="left:${x - 3}px;top:${y - 3}px;background:${color};box-shadow:0 0 6px ${color};"></div>`;
    }).join('');
}

function filterSeverity(severity, button) {
    currentFilter = severity;
    document.querySelectorAll('.live-filter-btn').forEach(item => item.classList.remove('active'));
    button.classList.add('active');
    renderAttacks(allAttacks);
}

async function blockAttack(id) {
    try {
        await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
        showToast('Attaquant bloqué avec succès.', 'success');
        fetchLiveAttacks();
    } catch (error) {
        showToast('Le blocage a échoué.', 'error');
    }
}

function blockLastAttacker() {
    if (lastAttackId) {
        blockAttack(lastAttackId);
    } else {
        showToast('Aucun attaquant récent à bloquer.', 'info');
    }
}

function viewAttack(id) {
    window.location.href = `/attacks/${id}`;
}

fetchLiveAttacks();
window.setInterval(fetchLiveAttacks, 3000);
</script>
@endpush
