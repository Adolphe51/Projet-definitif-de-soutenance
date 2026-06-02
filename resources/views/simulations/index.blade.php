@extends('layouts.app')
@section('title', 'Simulations')
@section('page-title', 'Laboratoire de simulation')
@section('page-subtitle', 'Espace isolé pour démontrer des scénarios d’attaque sans mélanger la partie soutenance avec la supervision opérationnelle.')

@section('content')
    @php
        $runningCount = $simulations->where('status', 'running')->count();
        $completedCount = $simulations->where('status', 'completed')->count();
        $highIntensityCount = $simulations->where('intensity', 'high')->count();
        $totalPackets = $simulations->sum('packets_sent');
    @endphp

    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Environnement de démonstration</span>
            <h2>Un panneau de simulation clair pour lancer, suivre et expliquer les scénarios de test.</h2>
            <p>
                Le laboratoire reste volontairement séparé du dashboard et du traitement des incidents,
                afin de garder une navigation propre entre démonstration pédagogique et usage opérationnel.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('attacks.live') }}" class="btn btn-primary">Voir le flux temps réel</a>
                <a href="{{ route('attacks.index') }}" class="btn btn-secondary-outline">Retour aux incidents</a>
            </div>
        </div>

        <div class="dashboard-health {{ $runningCount > 0 ? 'dashboard-health--medium' : 'dashboard-health--low' }}">
            <div class="dashboard-health-label">État du laboratoire</div>
            <div class="dashboard-health-value">{{ $runningCount > 0 ? 'Simulation en cours' : 'Prêt pour une démonstration' }}</div>
            <div class="dashboard-health-meta">
                {{ $runningCount }} active(s) · {{ $completedCount }} terminée(s) · {{ number_format($totalPackets) }} paquets simulés
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong>{{ count($types) }}</strong>
                    <span>types disponibles</span>
                </div>
                <div>
                    <strong>{{ $highIntensityCount }}</strong>
                    <span>scénarios élevés</span>
                </div>
            </div>
        </div>
    </section>

    <section class="attacks-overview-grid">
        <article class="attacks-overview-card attacks-overview-card--neutral">
            <span class="attacks-overview-label">Historique</span>
            <strong>{{ $simulations->count() }}</strong>
            <p>Total des démonstrations enregistrées dans le laboratoire.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--medium">
            <span class="attacks-overview-label">En cours</span>
            <strong>{{ $runningCount }}</strong>
            <p>Scénarios actuellement actifs ou récemment relancés.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--high">
            <span class="attacks-overview-label">Intensité haute</span>
            <strong>{{ $highIntensityCount }}</strong>
            <p>Cas plus démonstratifs pour montrer l’impact de la détection.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--critical">
            <span class="attacks-overview-label">Paquets simulés</span>
            <strong>{{ number_format($totalPackets) }}</strong>
            <p>Volume cumulé généré par les exercices de simulation.</p>
        </article>
    </section>

    <div class="sim-layout-grid">
        <section class="card dashboard-panel sim-launch-card">
            <div class="section-header">
                <div>
                    <div class="section-title">Lancer un scénario</div>
                    <p class="section-intro">Choisis un type d’attaque, une cible de démonstration et un niveau d’intensité.</p>
                </div>
            </div>

            <div class="sim-form-grid">
                <label class="filter-field">
                    <span class="form-label">Type d’attaque</span>
                    <select class="form-control" id="sim-type">
                        @foreach($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="filter-field">
                    <span class="form-label">IP cible</span>
                    <input type="text" class="form-control" id="sim-target" value="192.168.1.100" placeholder="ex: 10.0.0.1">
                </label>

                <div class="filter-field">
                    <span class="form-label">Durée de démonstration</span>
                    <input
                        type="range"
                        class="form-control"
                        id="sim-duration"
                        min="5"
                        max="120"
                        value="30"
                        oninput="document.getElementById('dur-display').textContent = this.value + 's'"
                        style="padding-inline: 0;"
                    >
                    <div class="sim-progress-meta">
                        <span id="dur-display">30s</span>
                    </div>
                    <div class="sim-scale-row">
                        <span>5s</span>
                        <span>60s</span>
                        <span>120s</span>
                    </div>
                </div>

                <div class="filter-field">
                    <span class="form-label">Intensité</span>
                    <div class="sim-intensity-grid">
                        <button type="button" class="sim-intensity-option" style="--sim-opt-color: var(--accent-green);" onclick="selectIntensity('low', this)">
                            <span class="sim-intensity-icon">🟢</span>
                            Faible
                        </button>
                        <button type="button" class="sim-intensity-option selected" style="--sim-opt-color: var(--accent-yellow);" onclick="selectIntensity('medium', this)">
                            <span class="sim-intensity-icon">🟡</span>
                            Moyen
                        </button>
                        <button type="button" class="sim-intensity-option" style="--sim-opt-color: var(--accent-red);" onclick="selectIntensity('high', this)">
                            <span class="sim-intensity-icon">🔴</span>
                            Élevé
                        </button>
                    </div>
                    <input type="hidden" id="sim-intensity" value="medium">
                </div>
            </div>

            <div class="attacks-filter-actions">
                <button class="btn btn-warning" id="launch-btn" onclick="launchSimulation()">
                    Lancer la simulation
                </button>
                <button class="btn btn-danger" id="stop-btn" onclick="stopSimulation()" style="display: none;">
                    Arrêter
                </button>
            </div>

            <div class="sim-progress-panel" id="sim-progress" style="display: none;">
                <div class="sim-progress-head">
                    <span class="sim-progress-label">Simulation en cours</span>
                    <span id="sim-elapsed" class="sim-progress-time">0s</span>
                </div>

                <div class="sim-progress-track">
                    <div class="sim-progress-fill" id="progress-fill"></div>
                </div>

                <div class="sim-progress-meta">
                    <span>Paquets envoyés: <strong id="sim-packets">0</strong></span>
                    <span id="sim-percent">0%</span>
                </div>
            </div>

            <div class="sim-log-shell">
                <div class="section-title">Journal de simulation</div>
                <div class="sim-log" id="sim-log">
                    <div class="log-line info">// Journal de simulation prêt</div>
                </div>
            </div>
        </section>

        <div class="sim-side-stack">
            <section class="card dashboard-panel sim-stage-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Flux de simulation</div>
                        <p class="section-intro">Visualisation instantanée des événements produits pendant la démonstration.</p>
                    </div>
                    <span id="sim-badge" class="badge badge-info">EN ATTENTE</span>
                </div>

                <div id="sim-feed" class="sim-feed">
                    <div class="empty-state">
                        <div class="empty-state-icon">⚗️</div>
                        <p class="empty-state-title">Aucun flux pour le moment</p>
                        <p class="empty-state-text">Lance un scénario pour voir apparaître les attaques simulées et leur sévérité.</p>
                    </div>
                </div>
            </section>

            <section class="card dashboard-panel sim-history-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Historique des simulations</div>
                        <p class="section-intro">Archive des essais réalisés avec leur cible, leur intensité et leur statut final.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Cible</th>
                                <th>Durée</th>
                                <th>Intensité</th>
                                <th>Paquets</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody id="sim-history">
                            @forelse($simulations as $sim)
                                <tr>
                                    <td class="mono text-muted-small">{{ $sim->name }}</td>
                                    <td><span class="badge badge-info">{{ $sim->attack_type }}</span></td>
                                    <td class="ip-addr">{{ $sim->target_ip }}</td>
                                    <td>{{ $sim->duration_seconds }}s</td>
                                    <td>
                                        @if($sim->intensity === 'high')
                                            <span class="badge badge-critical">Élevée</span>
                                        @elseif($sim->intensity === 'medium')
                                            <span class="badge badge-warning">Moyenne</span>
                                        @else
                                            <span class="badge badge-success">Faible</span>
                                        @endif
                                    </td>
                                    <td class="mono">{{ number_format($sim->packets_sent) }}</td>
                                    <td><span class="sim-status {{ $sim->status }}">{{ strtoupper($sim->status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="sim-history-empty">Aucune simulation enregistrée pour le moment.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let currentSimId = null;
let simInterval = null;
let simStartTime = null;
let simDuration = 30;
let simPackets = 0;
let simulationAudioContext = null;
let simulationSignalTimers = [];
const simulationRoutes = {
    launch: @json(route('simulations.launch')),
    simulate: @json(route('simulations.api.simulate')),
    stopBase: @json(url('/simulations/stop')),
};

function selectIntensity(value, element) {
    document.querySelectorAll('.sim-intensity-option').forEach(option => option.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('sim-intensity').value = value;
}

function getSimulationAudioContext() {
    if (!simulationAudioContext) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
            return null;
        }

        simulationAudioContext = new AudioContextClass();
    }

    if (simulationAudioContext.state === 'suspended') {
        simulationAudioContext.resume();
    }

    return simulationAudioContext;
}

function playSimulationTone(frequency, duration, volume) {
    const context = getSimulationAudioContext();

    if (!context) {
        return;
    }

    const oscillator = context.createOscillator();
    const gain = context.createGain();

    oscillator.type = 'triangle';
    oscillator.frequency.value = frequency;
    gain.gain.value = volume;

    oscillator.connect(gain);
    gain.connect(context.destination);

    oscillator.start();
    oscillator.stop(context.currentTime + duration);
}

function startSimulationSignal(level) {
    stopSimulationSignal();

    if (level !== 'high') {
        return;
    }

    simulationSignalTimers.push(window.setTimeout(() => playSimulationTone(720, 0.14, 0.035), 0));
    simulationSignalTimers.push(window.setTimeout(() => playSimulationTone(880, 0.14, 0.03), 220));
}

function stopSimulationSignal() {
    simulationSignalTimers.forEach(timer => window.clearTimeout(timer));
    simulationSignalTimers = [];
}

async function launchSimulation() {
    const type = document.getElementById('sim-type').value;
    const target = document.getElementById('sim-target').value;
    const duration = parseInt(document.getElementById('sim-duration').value, 10);
    const intensity = document.getElementById('sim-intensity').value;

    if (!target.match(/^(\d{1,3}\.){3}\d{1,3}$/)) {
        showToast('Adresse IP invalide.', 'error');
        return;
    }

    simDuration = duration;
    simPackets = 0;

    try {
        const response = await csrfFetch(simulationRoutes.launch, {
            method: 'POST',
            body: JSON.stringify({ attack_type: type, target_ip: target, duration, intensity })
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'La simulation n’a pas pu être démarrée.');
        }

        if (data.success) {
            currentSimId = data.simulation_id;
            simStartTime = Date.now();
            document.getElementById('sim-progress').style.display = 'grid';
            document.getElementById('launch-btn').style.display = 'none';
            document.getElementById('stop-btn').style.display = 'inline-flex';
            document.getElementById('sim-badge').textContent = 'EN COURS';
            document.getElementById('sim-badge').className = 'badge badge-success';

            addLog(`[${now()}] Simulation ${type} démarrée vers ${target}`, 'info');
            addLog(`[${now()}] Intensité: ${intensity} | Durée: ${duration}s`, 'info');
            simInterval = window.setInterval(runSimStep, 1500);
            showToast(`Simulation ${type} lancée vers ${target}.`, 'success');
            startSimulationSignal(intensity);
        }
    } catch (error) {
        showToast(error.message || 'La simulation n’a pas pu être démarrée.', 'error');
    }
}

async function runSimStep() {
    const elapsed = (Date.now() - simStartTime) / 1000;
    const percent = Math.min((elapsed / simDuration) * 100, 100);

    document.getElementById('progress-fill').style.width = `${percent}%`;
    document.getElementById('sim-elapsed').textContent = `${Math.floor(elapsed)}s`;
    document.getElementById('sim-percent').textContent = `${Math.round(percent)}%`;

    if (elapsed >= simDuration) {
        stopSimulation(true);
        return;
    }

    try {
        const simulateUrl = `${simulationRoutes.simulate}?simulation_id=${encodeURIComponent(currentSimId)}`;
        const response = await csrfFetch(simulateUrl, {
            method: 'POST',
            body: JSON.stringify({})
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Erreur pendant la génération des événements.');
        }

        if (data.status === 'completed') {
            stopSimulation(true);
            return;
        }

        if (data.status === 'failed') {
            throw new Error(data.message || 'La simulation a échoué.');
        }

        if (data.attack) {
            const attack = data.attack;
            simPackets += attack.packets || 0;
            document.getElementById('sim-packets').textContent = Number(simPackets).toLocaleString();

            addLog(
                `[${now()}] PKT→ ${attack.source_ip} (${attack.city}, ${attack.country}) | ${attack.severity.toUpperCase()} | ${Number(attack.packets).toLocaleString()} pkts`,
                attack.severity === 'critical' ? 'error' : attack.severity === 'high' ? 'warn' : ''
            );

            prependFeed(attack);
        }
    } catch (error) {
        addLog(`[${now()}] ${error.message || 'Erreur interne de simulation.'}`, 'error');
        stopSimulation();
        showToast(error.message || 'La simulation a été interrompue.', 'error');
    }
}

function prependFeed(attack) {
    const feed = document.getElementById('sim-feed');
    const item = document.createElement('div');

    item.className = 'sim-feed-item';
    item.innerHTML = `
        <span class="sim-feed-time">${now()}</span>
        <span class="badge badge-${attack.severity}">${attack.severity.toUpperCase()}</span>
        <span class="sim-feed-text">${attack.type} · <span class="ip-addr">${attack.source_ip}</span> · ${attack.country}</span>
        <span class="sim-feed-volume">${Number(attack.packets).toLocaleString()} pkts</span>
    `;

    if (feed.querySelector('.empty-state')) {
        feed.innerHTML = '';
    }

    feed.insertBefore(item, feed.firstChild);

    if (feed.children.length > 30) {
        feed.removeChild(feed.lastChild);
    }
}

function stopSimulation(completed = false) {
    window.clearInterval(simInterval);
    simInterval = null;
    document.getElementById('launch-btn').style.display = 'inline-flex';
    document.getElementById('stop-btn').style.display = 'none';
    document.getElementById('sim-badge').textContent = completed ? 'TERMINÉ' : 'ARRÊTÉ';
    document.getElementById('sim-badge').className = 'badge badge-info';
    document.getElementById('sim-progress').style.display = 'none';

    if (currentSimId && !completed) {
        csrfFetch(`${simulationRoutes.stopBase}/${currentSimId}`, { method: 'POST' });
    }

    addLog(`[${now()}] Simulation ${completed ? 'complétée' : 'arrêtée'} | Paquets totaux: ${Number(simPackets).toLocaleString()}`, 'info');
    stopSimulationSignal();
    showToast(`Simulation ${completed ? 'terminée' : 'arrêtée'} · ${Number(simPackets).toLocaleString()} paquets envoyés.`, 'info');
    currentSimId = null;
}

function addLog(message, level = '') {
    const log = document.getElementById('sim-log');
    const line = document.createElement('div');

    line.className = `log-line ${level}`;
    line.textContent = message;
    log.appendChild(line);
    log.scrollTop = log.scrollHeight;
}

function now() {
    return new Date().toLocaleTimeString('fr-FR', { hour12: false });
}
</script>
@endpush
