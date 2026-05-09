@extends('layouts.app')
@section('title', 'Alertes')
@section('page-title', 'Centre d’alertes')
@section('page-subtitle', 'Lecture corrélée des événements de sécurité issus de l’authentification, du mini site métier, du réseau et des simulations manuelles.')

@section('content')
    @php
        $admin = auth()->user()?->hasRole('admin');
        $latestAt = $summary['latestAt'] ? \Illuminate\Support\Carbon::parse($summary['latestAt']) : null;
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

    <section class="dashboard-hero alerts-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Corrélation</span>
            <h2>Un centre d’alertes qui raconte clairement ce qui s’est passé et pourquoi.</h2>
            <p>
                Les signaux issus de l’authentification, des actions du mini site, des attaques réseau
                et des simulations manuelles sont lus ici dans une seule vue cohérente.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('intranet.index') }}" class="btn btn-primary">Retour au mini site</a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary-outline">Retour au dashboard</a>
            </div>
        </div>

        <div class="dashboard-health {{ $summary['critical'] > 0 ? 'dashboard-health--critical' : 'dashboard-health--low' }}">
            <div class="dashboard-health-label">Niveau d’alerte</div>
            <div class="dashboard-health-value">{{ $summary['critical'] > 0 ? 'Priorité d’analyse élevée' : 'File d’alertes maîtrisée' }}</div>
            <div class="dashboard-health-meta">
                {{ $summary['critical'] }} critique(s) · {{ $summary['unread'] }} non lue(s)
                @if($latestAt)
                    · dernière alerte {{ $latestAt->diffForHumans() }}
                @endif
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong>{{ $summary['high'] + $summary['critical'] }}</strong>
                    <span>priorité haute</span>
                </div>
                <div>
                    <strong>{{ $summary['total'] }}</strong>
                    <span>historique total</span>
                </div>
            </div>
        </div>
    </section>

    <section class="attacks-overview-grid">
        <article class="attacks-overview-card attacks-overview-card--critical">
            <span class="attacks-overview-label">Audits auth 24h</span>
            <strong>{{ $summary['authAudit24h'] }}</strong>
            <p>Connexions et validations OTP visibles pour démontrer l’entrée sécurisée.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--high">
            <span class="attacks-overview-label">Mini site 24h</span>
            <strong>{{ $summary['intranetAudit24h'] }}</strong>
            <p>Actions métier auditées et potentiellement corrélées à une détection.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--medium">
            <span class="attacks-overview-label">Simulations</span>
            <strong>{{ $summary['manualSimulations'] }}</strong>
            <p>Alertes produites volontairement dans le laboratoire de démonstration.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--neutral">
            <span class="attacks-overview-label">Détections</span>
            <strong>{{ $summary['attackAlerts'] }}</strong>
            <p>Alertes directement liées aux attaques collectées et analysées.</p>
        </article>
    </section>

    <section class="card dashboard-panel">
        <div class="section-header">
            <div>
                <div class="section-title">Poste de contrôle</div>
                <p class="section-intro">Tester le son, acquitter les alertes et surveiller l’arrivée de nouveaux signaux sans génération automatique cachée.</p>
            </div>

            <div class="alerts-toolbar">
                <button class="btn btn-warning btn-sm" onclick="triggerManualAlarm()">Test alarme</button>
                @if($admin)
                    <button class="btn btn-primary btn-sm" onclick="acknowledgeAll()">Tout marquer comme lu</button>
                @endif
            </div>
        </div>

        <div class="live-indicator-bar">
            <span class="live-indicator-dot"></span>
            <span>Écoute active des nouvelles alertes</span>
            <span class="live-indicator-note" id="new-alert-notif">Aucune nouvelle alerte depuis le chargement.</span>
        </div>
    </section>

    <div class="dashboard-grid">
        <section class="card dashboard-panel">
            <div class="section-header">
                <div>
                    <div class="section-title">File des alertes</div>
                    <p class="section-intro">Chaque carte explicite la sévérité, l’origine, le contexte et l’accès direct à l’attaque liée lorsqu’elle existe.</p>
                </div>
            </div>

            <div class="alert-list-stack" id="alerts-list-container">
                @forelse($alerts as $alert)
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
                    <article class="alert-card alert-card--center sev-{{ $alert->severity }} {{ !$alert->acknowledged ? 'unread' : '' }}" id="alert-{{ $alert->id }}">
                        <div class="alert-card-icon" aria-hidden="true">
                            {{ $alert->severity === 'critical' ? '💀' : ($alert->severity === 'high' ? '🔴' : ($alert->severity === 'medium' ? '⚠️' : '✅')) }}
                        </div>

                        <div class="alert-card-body">
                            <div class="alert-card-head">
                                <div>
                                    <div class="alert-title">{{ $alert->title }}</div>
                                    <div class="alert-msg">{{ $alert->message }}</div>
                                </div>

                                @if(!$alert->acknowledged)
                                    <span class="badge badge-critical alert-fresh-badge">Non lue</span>
                                @endif
                            </div>

                            <div class="alert-card-meta">
                                <span class="badge badge-{{ $alert->severity }}">{{ strtoupper($alert->severity) }}</span>
                                <span class="badge badge-info">{{ $originLabel }}</span>
                                @if($alert->type)
                                    <span class="badge badge-primary">{{ strtoupper($alert->type) }}</span>
                                @endif
                                <span class="mono text-muted-small">{{ $alert->created_at->diffForHumans() }}</span>
                                <span class="text-muted-small">{{ $alert->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            @if($alert->attack)
                                <div class="alert-card-meta">
                                    <span class="badge badge-{{ $alert->attack->severity }}">{{ $alert->attack->type }}</span>
                                    <span class="text-muted-small">{{ $alert->attack->source_ip }} → {{ $alert->attack->target_ip }}</span>
                                    @if($alert->attack->rule)
                                        <span class="text-muted-small">Règle: {{ $alert->attack->rule->name ?? $alert->attack->rule_id }}</span>
                                    @endif
                                </div>
                            @endif

                            @if($alert->attack_id)
                                <div class="alert-card-link">
                                    <a href="{{ route('attacks.show', $alert->attack_id) }}" class="btn btn-secondary-outline btn-sm">
                                        Consulter l’attaque liée #{{ $alert->attack_id }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="alert-card-actions">
                            <button class="btn btn-secondary-outline btn-sm" onclick="playAlertSound('{{ $alert->severity }}')">Son</button>

                            @if($admin)
                                @if(!$alert->acknowledged)
                                    <button class="btn btn-success btn-sm" onclick="acknowledgeAlert({{ $alert->id }}, this)">Acquitter</button>
                                @else
                                    <span class="alert-acknowledged-label">Acquittée</span>
                                @endif
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">🔕</div>
                        <p class="empty-state-title">Aucune alerte active</p>
                        <p class="empty-state-text">Les prochaines alertes apparaîtront ici lorsqu’un événement réel ou une simulation manuelle sera remonté.</p>
                    </div>
                @endforelse
            </div>

            <div class="pagination-wrap">
                {{ $alerts->links() }}
            </div>
        </section>

        <aside class="stack-md">
            <section class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Corrélation récente</div>
                        <p class="section-intro">Ce que le système a effectivement journalisé autour des connexions, actions métier et traitements.</p>
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
                            <span>{{ $audit->actor?->nom ?? $audit->actor?->email ?? 'Système' }}</span>
                            <div class="feed-title">
                                <span class="badge badge-{{ $badge }}">{{ strtoupper($audit->importance) }}</span>
                                <span class="mono text-muted-small">{{ $audit->created_at->diffForHumans() }}</span>
                            </div>
                            @if($audit->ip_address)
                                <span class="text-muted-small">{{ $audit->ip_address }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">📘</div>
                            <p class="empty-state-title">Aucune corrélation récente</p>
                            <p class="empty-state-text">Les audits associés à la démonstration apparaîtront ici.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
<script>
async function acknowledgeAlert(id, btn) {
    btn.disabled = true;

    try {
        await csrfFetch(`/alerts/acknowledge/${id}`, { method: 'POST' });

        const card = document.getElementById(`alert-${id}`);
        card.classList.remove('unread');

        const badge = card.querySelector('.alert-fresh-badge');
        if (badge) {
            badge.remove();
        }

        btn.outerHTML = '<span class="alert-acknowledged-label">Acquittée</span>';
        updateAlertCount();
        showToast('Alerte acquittée.', 'success', 2500);
    } catch (error) {
        btn.disabled = false;
        showToast('Impossible d’acquitter cette alerte.', 'error');
    }
}

async function acknowledgeAll() {
    try {
        await csrfFetch('/alerts/clear-all', { method: 'POST' });

        document.querySelectorAll('.alert-card.unread').forEach(card => {
            card.classList.remove('unread');
            card.querySelector('.alert-fresh-badge')?.remove();

            const actionSlot = card.querySelector('.alert-card-actions');
            const button = actionSlot?.querySelector('.btn-success');

            if (button) {
                button.outerHTML = '<span class="alert-acknowledged-label">Acquittée</span>';
            }
        });

        updateAlertCount();
        showToast('Toutes les alertes ont été marquées comme lues.', 'success');
    } catch (error) {
        showToast('La mise à jour globale a échoué.', 'error');
    }
}

function updateAlertCount() {
    const remaining = document.querySelectorAll('.alert-card.unread').length;
    const topbar = document.getElementById('topbar-alert-count');
    const nav = document.getElementById('nav-alert-count');

    if (topbar) {
        topbar.textContent = remaining;
    }

    if (nav) {
        nav.textContent = remaining;
    }
}

let alertAudioContext = null;

function getAlertAudioContext() {
    if (!alertAudioContext) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
            return null;
        }

        alertAudioContext = new AudioContextClass();
    }

    if (alertAudioContext.state === 'suspended') {
        alertAudioContext.resume();
    }

    return alertAudioContext;
}

function playTone(frequency, duration, volume) {
    const context = getAlertAudioContext();

    if (!context) {
        return;
    }

    const oscillator = context.createOscillator();
    const gainNode = context.createGain();

    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(frequency, context.currentTime);
    gainNode.gain.setValueAtTime(volume, context.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.001, context.currentTime + duration);

    oscillator.connect(gainNode);
    gainNode.connect(context.destination);

    oscillator.start();
    oscillator.stop(context.currentTime + duration);
}

function playAlertSound(severity) {
    const presets = {
        critical: [[880, 0.18, 0.22], [660, 0.16, 0.18], [990, 0.22, 0.2]],
        high: [[760, 0.16, 0.18], [620, 0.14, 0.16]],
        medium: [[640, 0.14, 0.12]],
        low: [[520, 0.12, 0.1]],
    };

    (presets[severity] || presets.low).forEach(([frequency, duration, volume], index) => {
        setTimeout(() => playTone(frequency, duration, volume), index * 140);
    });
}

function triggerManualAlarm() {
    ['critical', 'high', 'medium'].forEach((severity, index) => {
        setTimeout(() => playAlertSound(severity), index * 240);
    });
    showToast('Test alarme déclenché.', 'warning');
}

let lastAlertId = {{ $alerts->first()?->id ?? 0 }};

async function pollUnreadAlerts() {
    try {
        const response = await fetch('/alerts/unread');
        const data = await response.json();

        const note = document.getElementById('new-alert-notif');
        if (!note) {
            return;
        }

        if (data.count > 0 && data.alerts[0]?.id > lastAlertId) {
            lastAlertId = data.alerts[0].id;
            note.innerHTML = `${data.count} nouvelle(s) alerte(s) détectée(s). <a href="/alerts">Actualiser</a>`;
        } else {
            note.textContent = 'Aucune nouvelle alerte depuis le chargement.';
        }
    } catch (error) {
        console.error(error);
    }
}

setInterval(pollUnreadAlerts, 8000);
</script>
@endpush
