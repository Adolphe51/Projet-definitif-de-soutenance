@extends('layouts.app')
@section('title', 'Détail Attaque #' . $attack->id)
@section('page-title', 'Fiche incident #' . $attack->id)
@section('page-subtitle', 'Analyse détaillée de l’attaque, contexte technique et actions SOC associées.')

@section('content')
    @php
        $canManageAttack = auth()->user()?->hasRole('admin');
        $isBlocked = \App\Models\BlockedIp::isBlocked($attack->source_ip) || $attack->status === 'blocked';
        $scores = [
            'critical' => 95,
            'high' => 75,
            'medium' => 50,
            'low' => 25,
        ];
        $score = $scores[$attack->severity] ?? 40;
        $scoreColors = [
            'critical' => '#ff5d7a',
            'high' => '#fb923c',
            'medium' => '#facc15',
            'low' => '#3ddc97',
        ];
        $scoreColor = $scoreColors[$attack->severity] ?? '#4adeff';
        $deg = ($score / 100) * 360;
        $demoPayloads = [
            'DDoS' => 'UDP flood detecte sur plusieurs ports applicatifs avec un rythme anormalement eleve.',
            'SQL Injection' => 'Suite de requetes suspectes avec signatures UNION SELECT et contournement d authentification.',
            'XSS' => 'Charge utile de script injectee dans un champ applicatif avec tentative d exfiltration de session.',
            'Brute Force' => 'Serie de tentatives d authentification sur un compte expose avec rotation rapide des mots de passe.',
            'Port Scan' => 'Balayage multi ports sur la cible avec enumeration des services disponibles.',
            'Ransomware' => 'Comportement de chiffrement massif et connexions vers une infrastructure de commande.',
            'MITM' => 'Alteration du trafic et signatures de poisoning reseau detectees sur le segment.',
        ];
        $payloadText = trim((string) $attack->payload) !== ''
            ? $attack->payload
            : ($demoPayloads[$attack->type] ?? 'Aucune charge utile exploitable n a ete conservee pour cet incident.');
    @endphp

    <div
        id="attack-show-page"
        data-severity="{{ $attack->severity }}"
        data-block-url="{{ route('attacks.block', ['id' => $attack->id]) }}"
        data-unblock-url="{{ route('attacks.unblock', ['id' => $attack->id]) }}"
        data-status-url="{{ route('attacks.status', ['id' => $attack->id]) }}"
    >
        <section class="incident-hero">
            <div class="incident-hero-main">
                <a href="{{ route('attacks.index') }}" class="btn btn-secondary-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>

                <div class="attack-summary attack-summary--hero">
                    <div class="attack-icon">{{ $attack->type_icon }}</div>
                    <div>
                        <div class="attack-title">{{ $attack->type }}</div>
                        <div class="attack-meta">
                            Détectée {{ $attack->created_at->diffForHumans() }} · Source {{ $attack->source_ip }} · Cible {{ $attack->target_ip }}
                        </div>
                    </div>
                </div>

                <div class="incident-hero-badges">
                    <x-badge class="badge-{{ $attack->severity }} badge-small">
                        {{ $attack->severity_icon }} {{ strtoupper($attack->severity) }}
                    </x-badge>
                    <x-badge class="badge-{{ $attack->status }} badge-small">{{ strtoupper($attack->status) }}</x-badge>
                    @if($attack->is_simulation)
                        <x-badge class="badge-sim">SIMULATION</x-badge>
                    @endif
                </div>
            </div>

            <div class="incident-hero-side">
                <div class="incident-key-stat">
                    <span>Score de menace</span>
                    <strong>{{ $score }}/100</strong>
                </div>
                <div class="incident-key-stat">
                    <span>Incident</span>
                    <strong>{{ $attack->incident_id ?: 'Non corrélé' }}</strong>
                </div>
                <div class="incident-key-stat">
                    <span>Règle</span>
                    <strong>{{ $attack->rule?->name ?? $attack->rule_id ?? 'Détection interne' }}</strong>
                </div>
            </div>
        </section>

        <section class="incident-summary-grid">
            <div class="incident-summary-card">
                <span>Source</span>
                <strong>{{ $attack->source_ip }}</strong>
                <small>{{ $attack->city ?: 'Ville inconnue' }}, {{ $attack->country ?: 'Pays inconnu' }}</small>
            </div>
            <div class="incident-summary-card">
                <span>Cible</span>
                <strong>{{ $attack->target_ip }}@if($attack->target_port):{{ $attack->target_port }}@endif</strong>
                <small>{{ $attack->protocol ?: 'Protocole non précisé' }}</small>
            </div>
            <div class="incident-summary-card">
                <span>Volume</span>
                <strong>{{ number_format($attack->packet_count) }} paquets</strong>
                <small>{{ $attack->bandwidth_mbps }} Mbps observés</small>
            </div>
            <div class="incident-summary-card">
                <span>Traitement</span>
                <strong>{{ $isBlocked ? 'Contenu' : 'À traiter' }}</strong>
                <small>{{ $attack->updated_at->format('d/m/Y H:i') }}</small>
            </div>
        </section>

        <div class="detail-grid detail-grid--incident">
            <div class="stack-md">
                <div class="card dashboard-panel">
                    <div class="section-title section-title--spaced">Synthèse incident</div>
                    <div class="info-block">
                        <div class="info-row">
                            <span class="info-key">IP Source</span>
                            <span class="info-val"><span class="ip-addr info-val--small">{{ $attack->source_ip }}</span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Localisation</span>
                            <span class="info-val">🌍 {{ $attack->city ?: 'Inconnue' }}, {{ $attack->country ?: 'Inconnu' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Fournisseur</span>
                            <span class="info-val">{{ $attack->isp ?? 'Inconnu' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Coordonnées GPS</span>
                            <span class="info-val mono info-val--small">{{ $attack->latitude }}, {{ $attack->longitude }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">IP Cible</span>
                            <span class="info-val">
                                <span class="ip-addr">{{ $attack->target_ip }}</span>@if($attack->target_port):{{ $attack->target_port }}@endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Protocole</span>
                            <span class="info-val">{{ $attack->protocol ?: 'Non précisé' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Paquets reçus</span>
                            <span class="info-val text-accent-cyan">{{ number_format($attack->packet_count) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Bande passante</span>
                            <span class="info-val text-accent-orange">{{ $attack->bandwidth_mbps }} Mbps</span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Statut</span>
                            <span class="info-val">{{ strtoupper($attack->status) }}</span>
                        </div>
                    </div>

                    <div class="description-card">
                        <div class="description-label">Résumé analytique</div>
                        <div class="description-box">
                            {{ $attack->description ?: "L’attaque a été classée comme {$attack->type} avec une sévérité {$attack->severity}. Cette fiche centralise les éléments utiles à la démonstration SOC et à la prise de décision." }}
                        </div>
                    </div>
                </div>

                <div class="card dashboard-panel">
                    <div class="section-title section-title--spaced">Indicateurs techniques</div>
                    <div class="payload-box" id="payload-display">{{ $payloadText }}</div>
                </div>

                <div class="card dashboard-panel">
                    <div class="section-title section-title--spaced">Timeline de traitement</div>
                    <div class="timeline-item">
                        <div>
                            <div class="timeline-title">Attaque détectée</div>
                            <div class="timeline-meta">{{ $attack->created_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                    <div class="timeline-item timeline-item--warning">
                        <div>
                            <div class="timeline-title">Analyse initiale</div>
                            <div class="timeline-meta">{{ $attack->created_at->copy()->addSeconds(2)->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                    @if($attack->rule)
                        <div class="timeline-item">
                            <div>
                                <div class="timeline-title">Règle corrélée</div>
                                <div class="timeline-meta">{{ $attack->rule->name }} · {{ $attack->rule->severity ?? 'niveau non précisé' }}</div>
                            </div>
                        </div>
                    @endif
                    @if($attack->alarm_triggered)
                        <div class="timeline-item timeline-item--danger">
                            <div>
                                <div class="timeline-title">Alerte renforcée</div>
                                <div class="timeline-meta">{{ $attack->created_at->copy()->addSeconds(3)->format('d/m/Y H:i:s') }}</div>
                            </div>
                        </div>
                    @endif
                    @if($isBlocked)
                        <div class="timeline-item timeline-item--success">
                            <div>
                                <div class="timeline-title">IP bloquée</div>
                                <div class="timeline-meta">{{ $attack->updated_at->format('d/m/Y H:i:s') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="stack-md">
                <div class="card dashboard-panel threat-card">
                    <div class="section-title section-title--center">Score de menace</div>
                    <div class="threat-score" style="--score-color:{{ $scoreColor }}; --score-deg:{{ $deg }}deg;">
                        <div class="threat-score-val">{{ $score }}</div>
                    </div>
                    <div class="text-highlight threat-level" style="--score-color:{{ $scoreColor }};">{{ strtoupper($attack->severity) }}</div>
                    <div class="text-muted-small">Score /100</div>
                </div>

                <div class="card dashboard-panel">
                    <div class="section-title section-title--spaced">Décision et actions</div>
                    <div class="action-grid">
                        @if($canManageAttack && ! $isBlocked)
                            <button class="btn btn-danger btn-center" id="block-btn" type="button">
                                <i class="fas fa-ban"></i> Bloquer l’IP source
                            </button>
                        @endif
                        @if($canManageAttack && $isBlocked)
                            <button class="btn btn-warning btn-center" id="unblock-btn" type="button">
                                <i class="fas fa-unlock"></i> Débloquer l’IP source
                            </button>
                        @endif
                        <a href="{{ route('geo.trace', $attack->source_ip) }}" class="btn btn-primary btn-center">
                            <i class="fas fa-crosshairs"></i> Tracer l’attaquant
                        </a>
                    </div>
                </div>

                <div class="card dashboard-panel">
                    <div class="section-title section-title--spaced">Contexte source</div>
                    <div class="location-panel">
                        <svg viewBox="0 0 300 160" class="location-map" xmlns="http://www.w3.org/2000/svg">
                            <rect width="300" height="160" fill="#050a0f" />
                            <g stroke="rgba(0,229,255,0.06)" stroke-width="0.5">
                                @for($i = 0; $i < 6; $i++)
                                    <line x1="{{ $i * 50 }}" y1="0" x2="{{ $i * 50 }}" y2="160" />
                                @endfor
                                @for($i = 0; $i < 4; $i++)
                                    <line x1="0" y1="{{ $i * 40 }}" x2="300" y2="{{ $i * 40 }}" />
                                @endfor
                            </g>
                            <circle cx="150" cy="80" r="8" fill="{{ $scoreColor }}" opacity="0.9" />
                            <circle cx="150" cy="80" r="8" fill="none" stroke="{{ $scoreColor }}" stroke-width="1.5" opacity="0.5">
                                <animate attributeName="r" from="8" to="25" dur="1.5s" repeatCount="indefinite" />
                                <animate attributeName="opacity" from="0.5" to="0" dur="1.5s" repeatCount="indefinite" />
                            </circle>
                            <text x="160" y="76" font-family="IBM Plex Mono" font-size="9" fill="{{ $scoreColor }}">{{ $attack->source_ip }}</text>
                            <text x="160" y="88" font-family="IBM Plex Mono" font-size="8" fill="rgba(255,255,255,0.4)">{{ $attack->city }}</text>
                        </svg>
                    </div>

                    <div class="location-summary">
                        <div class="location-row">
                            <span class="text-muted-small">Pays</span>
                            <strong>{{ $attack->country ?: 'Inconnu' }}</strong>
                        </div>
                        <div class="location-row">
                            <span class="text-muted-small">Ville</span>
                            <strong>{{ $attack->city ?: 'Inconnue' }}</strong>
                        </div>
                        <div class="location-row">
                            <span class="text-muted-small">ISP</span>
                            <strong>{{ $attack->isp ?: 'Inconnu' }}</strong>
                        </div>
                        <div class="location-row">
                            <span class="text-muted-small">Lat / Lon</span>
                            <strong class="mono location-small">{{ $attack->latitude }}, {{ $attack->longitude }}</strong>
                        </div>
                    </div>
                </div>

                @if($canManageAttack)
                    <div class="card dashboard-panel">
                        <div class="section-title section-title--spaced">Workflow SOC</div>
                        <div class="control-stack">
                            <div>
                                <label for="attack-status" class="form-label">Nouveau statut</label>
                                <select id="attack-status" class="form-control">
                                    <option value="investigating" @selected($attack->status === 'investigating')>Investigating</option>
                                    <option value="blocked" @selected($attack->status === 'blocked')>Blocked</option>
                                    <option value="false_positive" @selected($attack->status === 'false_positive')>False Positive</option>
                                    <option value="resolved" @selected($attack->status === 'resolved')>Resolved</option>
                                </select>
                            </div>
                            <div>
                                <label for="attack-comment" class="form-label">Commentaire</label>
                                <textarea id="attack-comment" rows="4" class="form-control" placeholder="Capture d'analyse, décision SOC, justification...">{{ old('comment') }}</textarea>
                            </div>
                            <button class="btn btn-primary btn-center" id="soc-action-btn" type="button">
                                <i class="fas fa-check-circle"></i> Mettre à jour le statut
                            </button>
                        </div>
                    </div>
                @endif

                @if($attack->comments->isNotEmpty())
                    <div class="card dashboard-panel">
                        <div class="section-title section-title--spaced">Journal SOC</div>
                        <div class="list-stack">
                            @foreach($attack->comments as $comment)
                                <div class="comment-card">
                                    <div class="comment-meta">
                                        <strong>{{ $comment->user?->name ?? 'Système' }}</strong>
                                        <span>{{ ucfirst($comment->status) }}</span>
                                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="comment-text">{{ $comment->comment ?? 'Aucune note' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($relatedAttacks->isNotEmpty())
                    <div class="card dashboard-panel">
                        <div class="section-title section-title--spaced">Attaques corrélées</div>
                        <div class="list-stack">
                            @foreach($relatedAttacks as $related)
                                <a href="{{ route('attacks.show', $related->id) }}" class="related-attack-card">
                                    <div><strong>#{{ $related->id }} {{ $related->type }}</strong></div>
                                    <div>{{ $related->source_ip }} · {{ $related->status }}</div>
                                    <div>{{ $related->created_at->diffForHumans() }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const attackPage = document.getElementById('attack-show-page');

        function bindClick(id, handler) {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('click', handler);
            }
        }

        async function blockThis() {
            const btn = document.getElementById('block-btn');
            const blockUrl = attackPage?.dataset.blockUrl;

            if (!blockUrl || !btn) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Blocage...';

            const res = await csrfFetch(blockUrl, { method: 'POST' });
            const data = await res.json();

            if (data.success) {
                showToast(data.message, data.already ? 'info' : 'success');
                window.location.reload();
            }
        }

        async function unblockThis() {
            const unblockUrl = attackPage?.dataset.unblockUrl;

            if (!unblockUrl) {
                return;
            }

            const res = await csrfFetch(unblockUrl, { method: 'POST' });
            const data = await res.json();

            if (data.success) {
                showToast(data.message, data.already ? 'info' : 'success');
                window.location.reload();
            }
        }

        async function updateAttackStatus() {
            const statusUrl = attackPage?.dataset.statusUrl;
            const status = document.getElementById('attack-status').value;
            const comment = document.getElementById('attack-comment').value;
            const button = document.getElementById('soc-action-btn');

            if (!statusUrl || !button) {
                return;
            }

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour...';

            const res = await csrfFetch(statusUrl, {
                method: 'POST',
                body: JSON.stringify({ status, comment }),
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message, data.already ? 'info' : 'success');
                window.location.reload();
            }
        }

        bindClick('block-btn', blockThis);
        bindClick('unblock-btn', unblockThis);
        bindClick('soc-action-btn', updateAttackStatus);
    </script>
@endpush
