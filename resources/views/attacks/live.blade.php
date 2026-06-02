@extends('layouts.app')
@section('title', 'Détection live')
@section('page-title', 'Flux d’evenements')
@section('page-subtitle', 'Journal recent des detections internes, externes et simulees, avec mise en avant des nouveaux evenements plutot qu’un simple rechargement permanent.')

@section('content')
    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Surveillance en direct</span>
            <h2>Un flux recent plus credible pour suivre les nouveautes sans rejouer en boucle le meme instantane.</h2>
            <p>
                Cette page sert a suivre les nouveaux evenements, distinguer interne, externe et simulation,
                puis ouvrir rapidement une fiche d incident lorsqu un signal merite une analyse.
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
                Le flux garde l historique recent en memoire et n ajoute que les nouveaux evenements detectes.
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
            <span class="live-indicator-note" id="live-stream-note">Le flux detectera et inserera les nouveaux evenements toutes les 3 secondes.</span>
        </div>
    </section>

    <div class="live-layout-grid">
        <section class="card dashboard-panel live-stream-shell">
            <div class="section-header">
                <div>
                    <div class="section-title">Flux d’evenements</div>
                    <p class="section-intro">Filtre par origine ou priorite, puis ouvre la fiche d’un incident en un clic.</p>
                </div>
            </div>

            <div class="live-toolbar">
                <div class="live-filter-bar">
                    <button class="live-filter-btn active" onclick="filterSeverity('all', this)">Toutes</button>
                    <button class="live-filter-btn" onclick="filterSeverity('internal', this)">Internes</button>
                    <button class="live-filter-btn" onclick="filterSeverity('external', this)">Externes</button>
                    <button class="live-filter-btn" onclick="filterSeverity('simulation', this)">Simulations</button>
                    <button class="live-filter-btn" onclick="filterSeverity('priority', this)">Prioritaires</button>
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
let eventHistory = [];
const seenAttackIds = new Set();
let liveAudioContext = null;
let isInitialLiveLoad = true;

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
        document.getElementById('c-packets').textContent = (data.attacks || [])
            .reduce((sum, attack) => sum + Number(attack.packet_count || 0), 0)
            .toLocaleString();

        const newEvents = (data.attacks || []).filter(attack => !seenAttackIds.has(attack.id));
        mergeLiveEvents(data.attacks || []);
        renderAttacks(eventHistory);
        updateRadar(eventHistory.filter(attack => attack.source_scope === 'external').slice(0, 8));

        const note = document.getElementById('live-stream-note');

        if (note) {
            note.textContent = newEvents.length > 0
                ? `${newEvents.length} nouvel(le)(s) evenement(s) ajoute(s) au flux.`
                : 'Aucun nouvel evenement depuis le dernier cycle.';
        }

        if (!isInitialLiveLoad) {
            newEvents.forEach((attack) => {
                if (attack.alarm && ['critical', 'high'].includes(attack.severity)) {
                    triggerLiveAlarm(attack.severity);
                    showToast(`${attack.type} detectee via ${attack.source_label}.`, attack.severity === 'critical' ? 'error' : 'warning');
                } else {
                    showToast(`${attack.type} detectee via ${attack.source_label}.`, 'info', 3000);
                }
            });
        }

        isInitialLiveLoad = false;
    } catch (error) {
        console.error(error);
    }
}

function mergeLiveEvents(attacks) {
    const indexed = new Map(eventHistory.map(attack => [attack.id, attack]));

    attacks.forEach((attack) => {
        if (!seenAttackIds.has(attack.id)) {
            seenAttackIds.add(attack.id);
            eventHistory.unshift(attack);
            indexed.set(attack.id, attack);
            return;
        }

        indexed.set(attack.id, { ...indexed.get(attack.id), ...attack });
    });

    eventHistory = Array.from(indexed.values())
        .sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime())
        .slice(0, 60);
}


function renderAttacks(attacks) {
    const filtered = attacks.filter((attack) => {
        if (currentFilter === 'all') {
            return true;
        }

        if (currentFilter === 'internal') {
            return attack.source_scope === 'internal';
        }

        if (currentFilter === 'external') {
            return attack.source_scope === 'external';
        }

        if (currentFilter === 'simulation') {
            return attack.source_channel === 'simulation';
        }

        if (currentFilter === 'priority') {
            return ['critical', 'high'].includes(attack.severity);
        }

        return true;
    });
    const stream = document.getElementById('attack-stream');

    if (!filtered.length) {
        stream.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">🛰️</div>
                <p class="empty-state-title">Aucun evenement pour ce filtre</p>
                <p class="empty-state-text">Change l origine ou attends le prochain cycle du flux recent.</p>
            </div>
        `;
        document.getElementById('last-attack-content').textContent = 'Aucun evenement recent pour ce filtre.';
        return;
    }

    stream.innerHTML = filtered.map(attack => `
        <div class="live-attack-row ${attack.severity}" onclick="viewAttack(${attack.id})">
            <div class="live-attack-icon">${attack.icon}</div>
            <div class="live-attack-info">
                <div class="live-attack-title">
                    <span>${attack.type}</span>
                    <span class="badge badge-${attack.severity}">${attack.severity.toUpperCase()}</span>
                    <span class="badge badge-info">${attack.source_label}</span>
                    ${attack.is_simulation ? '<span class="badge badge-simulation">SIM</span>' : ''}
                    ${attack.source_scope === 'internal' ? '<span class="badge badge-warning">INTERNE</span>' : '<span class="badge badge-primary">EXTERNE</span>'}
                    ${attack.status === 'blocked' ? '<span class="badge badge-success">BLOQUÉE</span>' : ''}
                </div>
                <div class="live-attack-meta">
                    <span class="ip-addr">${attack.source_ip}</span>
                    <span>${attack.source_scope === 'internal' ? 'Reseau local' : `${attack.city}, ${attack.country}`}</span>
                    <span>${attack.target_ip}</span>
                    <span>${attack.time}</span>
                </div>
                <div class="live-attack-desc">${attack.description || ''}</div>
            </div>
            <div class="live-attack-stats">
                <div class="live-attack-packets">${Number(attack.packet_count || 0).toLocaleString()}</div>
                <div class="live-attack-bw">${attack.bandwidth_mbps || 0} Mbps</div>
                <div class="dashboard-actions" style="margin-top: 0.45rem; justify-content: flex-end;">
                    ${attack.source_scope === 'external'
                        ? `<button class="btn btn-danger btn-sm" onclick="event.stopPropagation(); blockAttack(${attack.id})">Bloquer</button>`
                        : `<button class="btn btn-secondary-outline btn-sm" onclick="event.stopPropagation(); viewAttack(${attack.id})">Ouvrir</button>`}
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
            <div class="text-muted-small" style="margin-bottom: 0.35rem;">${last.source_label} · ${last.source_scope === 'internal' ? 'Reseau local' : `${last.city}, ${last.country}`}</div>
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
    const attack = eventHistory.find(item => item.id === id);

    if (attack?.source_scope === 'internal') {
        showToast('Les evenements internes sont analyses via leur fiche incident plutot que bloques ici.', 'info');
        return;
    }

    try {
        await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
        showToast('Attaquant bloqué avec succès.', 'success');
        fetchLiveAttacks();
    } catch (error) {
        showToast('Le blocage a échoué.', 'error');
    }
}

function blockLastAttacker() {
    const last = eventHistory.find(attack => attack.id === lastAttackId);

    if (last?.source_scope === 'internal') {
        showToast('La derniere source est interne. Ouvre plutot la fiche incident associee.', 'info');
        return;
    }

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
