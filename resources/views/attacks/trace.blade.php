@extends('layouts.app')
@section('title', 'Traçage ' . $ip)
@section('page-title', 'Traçage d’un attaquant')
@section('page-subtitle', 'Lecture simplifiée du parcours réseau, des données géographiques et de la dernière activité associée à cette IP.')

@section('content')
    @php
        $alarmAlreadyTriggered = (bool) ($attack?->alarm_triggered ?? false);
    @endphp
    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Trace IP</span>
            <h2>Suivre rapidement l’origine d’une adresse IP sans quitter le module incidents.</h2>
            <p>
                Cette vue rassemble les éléments utiles pour la soutenance : identification de l’IP,
                résumé géographique, pseudo traceroute et lien direct vers le dernier incident connu.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('attacks.index') }}" class="btn btn-primary">Retour aux attaques</a>
                @if($attack)
                    <a href="{{ route('attacks.show', $attack->id) }}" class="btn btn-secondary-outline">Voir la fiche liée</a>
                @endif
            </div>
        </div>

        <div class="dashboard-health {{ $attack?->severity === 'critical' ? 'dashboard-health--critical' : 'dashboard-health--medium' }}">
            <div class="dashboard-health-label">IP tracée</div>
            <div class="dashboard-health-value ip-addr">{{ $ip }}</div>
            <div class="dashboard-health-meta">
                {{ $geo['city'] }}, {{ $geo['country'] }} · {{ $geo['isp'] }}
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong>{{ $attack ? strtoupper($attack->severity) : 'N/A' }}</strong>
                    <span>dernière sévérité</span>
                </div>
                <div>
                    <strong>{{ $attack ? $attack->created_at->diffForHumans() : 'Aucune' }}</strong>
                    <span>dernière activité</span>
                </div>
            </div>
        </div>
    </section>

    <div class="trace-layout-grid">
        <div class="stack-md">
            <section class="trace-terminal-shell">
                <div class="section-title section-title--spaced">Traceroute simulé</div>
                <div class="trace-terminal">
                    <div style="color: #93c5fd; margin-bottom: 0.75rem;">$ traceroute {{ $ip }}</div>
                    <div id="trace-output"></div>
                </div>
            </section>

            <section class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Résultats géographiques</div>
                        <p class="section-intro">Résumé des informations disponibles pour situer et qualifier l’origine de l’adresse tracée.</p>
                    </div>
                </div>

                <div class="trace-geo-grid">
                    @foreach(['Pays' => $geo['country'], 'Ville' => $geo['city'], 'Latitude' => $geo['lat'] ?? 'N/A', 'Longitude' => $geo['lon'] ?? 'N/A', 'ISP' => $geo['isp'], 'ASN' => 'AS' . rand(10000,65000)] as $label => $value)
                        <article class="trace-geo-card">
                            <span>{{ $label }}</span>
                            <strong>{{ $value }}</strong>
                        </article>
                    @endforeach
                </div>
            </section>

            @if($attack)
                <section class="card dashboard-panel">
                    <div class="section-header">
                        <div>
                            <div class="section-title">Dernière attaque liée</div>
                            <p class="section-intro">Raccourci vers le dernier incident connu sur cette IP pour basculer rapidement en analyse.</p>
                        </div>
                    </div>

                    <div class="feed-item">
                        <div class="feed-icon">{{ $attack->type_icon }}</div>
                        <div class="feed-content">
                            <div class="feed-title">
                                <span>{{ $attack->type }}</span>
                                <span class="badge badge-{{ $attack->severity }}">{{ strtoupper($attack->severity) }}</span>
                            </div>
                            <div class="feed-details">{{ $attack->created_at->diffForHumans() }} · {{ $attack->target_ip }}</div>
                        </div>
                        <a href="{{ route('attacks.show', $attack->id) }}" class="btn btn-primary btn-sm">Voir détails</a>
                    </div>
                </section>
            @endif
        </div>

        <div class="trace-side-stack">
            <section class="card dashboard-panel">
                <div class="section-header">
                    <div class="section-title">Actions</div>
                </div>

                <div class="action-grid">
                    <button
                        class="btn {{ $isBlocked ? 'btn-success' : 'btn-danger' }} btn-center"
                        onclick="blockIP()"
                        @disabled(!$attack || $isBlocked)
                    >
                        {{ $isBlocked ? 'IP déjà bloquée' : 'Bloquer cette IP' }}
                    </button>
                    <button
                        class="btn {{ $alarmAlreadyTriggered ? 'btn-secondary-outline' : 'btn-warning' }} btn-center"
                        onclick="triggerTraceAlarm()"
                        @disabled(!$attack)
                    >
                        {{ $alarmAlreadyTriggered ? 'Alarme déjà déclenchée' : 'Déclencher une alarme' }}
                    </button>
                    <a href="{{ route('geo.attackers') }}" class="btn btn-secondary-outline btn-center">
                        Retour à la carte
                    </a>
                </div>
            </section>

            <section class="trace-warning-card">
                <div class="trace-warning-title">USAGE DÉFENSIF UNIQUEMENT</div>
                <div class="section-intro" style="margin: 0; color: var(--text-secondary);">
                    Ces données servent uniquement à l’analyse et à la défense du système. Elles ne doivent pas être utilisées à des fins offensives.
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const traceState = {
    blockUrl: @json($attack ? route('attacks.block', $attack->id) : null),
    alarmUrl: @json($attack ? route('attacks.alarm', $attack->id) : null),
    blocked: @json($isBlocked),
    alarmTriggered: @json($alarmAlreadyTriggered),
};

const hops = [
    '192.168.1.1',
    '10.{{ rand(0,255) }}.{{ rand(0,255) }}.1',
    '{{ preg_replace('/\.\d+$/', '.1', $ip) }}',
    '{{ preg_replace('/\.\d+$/', '.254', $ip) }}',
    '{{ $ip }}'
];

let traceIndex = 0;
const output = document.getElementById('trace-output');
let traceAudioContext = null;

function getTraceAudioContext() {
    if (!traceAudioContext) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
            return null;
        }

        traceAudioContext = new AudioContextClass();
    }

    if (traceAudioContext.state === 'suspended') {
        traceAudioContext.resume();
    }

    return traceAudioContext;
}

function playTraceTone(frequency, duration, volume) {
    const context = getTraceAudioContext();

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

function addHop() {
    if (traceIndex >= hops.length) {
        return;
    }

    const latency = (Math.random() * 50 + 5).toFixed(1);
    const line = document.createElement('div');
    line.textContent = ` ${traceIndex + 1}  ${hops[traceIndex].padEnd(18)}  ${latency} ms`;
    output.appendChild(line);
    traceIndex++;
    window.setTimeout(addHop, 550);
}

async function triggerTraceAlarm() {
    if (!traceState.alarmUrl) {
        showToast('Aucun incident lié disponible pour déclencher une alarme.', 'info');
        return;
    }

    if (traceState.alarmTriggered) {
        showToast('L’alarme est déjà déclenchée pour cette IP.', 'info');
        return;
    }

    try {
        const response = await csrfFetch(traceState.alarmUrl, { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            playTraceTone(760, 0.12, 0.035);
            window.setTimeout(() => playTraceTone(920, 0.12, 0.03), 170);
            showToast(data.message, data.already ? 'info' : 'warning');
            traceState.alarmTriggered = true;
            window.setTimeout(() => window.location.reload(), 800);
        }
    } catch (error) {
        showToast('Le déclenchement de l’alarme a échoué.', 'error');
    }
}

async function blockIP() {
    if (!traceState.blockUrl) {
        showToast('Aucun incident lié disponible pour effectuer un blocage.', 'info');
        return;
    }

    if (traceState.blocked) {
        showToast('Cette IP est déjà bloquée.', 'info');
        return;
    }

    try {
        const response = await csrfFetch(traceState.blockUrl, { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            showToast(data.message, data.already ? 'info' : 'success');
            traceState.blocked = true;
            window.setTimeout(() => window.location.reload(), 800);
        }
    } catch (error) {
        showToast('Le blocage a échoué.', 'error');
    }
}

window.setTimeout(addHop, 450);
</script>
@endpush
