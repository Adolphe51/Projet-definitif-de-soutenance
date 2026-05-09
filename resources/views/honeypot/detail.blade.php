@extends('layouts.app')
@section('title', 'Piège ' . $trap->name)
@section('page-title', 'Détail d’un piège honeypot')
@section('page-subtitle', 'Historique des interactions, preuves capturées et contexte détaillé d’un piège spécifique.')

@section('content')
    <section class="card dashboard-panel honeypot-detail-hero">
        <div class="honeypot-detail-summary">
            <div class="honeypot-detail-icon">🍯</div>
            <div>
                <div class="attack-title" style="font-size: 1.5rem;">{{ $trap->name }}</div>
                <div class="section-intro" style="margin-top: 0.2rem;">{{ $trap->description }}</div>
                <div class="hp-trap-meta-row" style="margin-top: 0.7rem;">
                    @if($trap->fake_service)
                        <span class="badge badge-info">{{ $trap->fake_service }}</span>
                    @endif
                    @if($trap->port)
                        <span class="badge badge-primary">:{{ $trap->port }}</span>
                    @endif
                    @if($trap->path)
                        <span class="mono text-muted-small">{{ $trap->path }}</span>
                    @endif
                </div>
            </div>
            <div class="honeypot-detail-counter">
                <strong>{{ $trap->interactions_count }}</strong>
                <span>interactions</span>
            </div>
        </div>
    </section>

    <div class="honeypot-detail-grid">
        <div class="stack-md">
            <section class="card dashboard-panel">
                <div class="section-header">
                    <div>
                        <div class="section-title">Historique complet</div>
                        <p class="section-intro">Tableau détaillé des connexions, credentials testés, actions réalisées et score de risque associé.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>IP source</th>
                                <th>Localisation</th>
                                <th>Credentials testés</th>
                                <th>User-Agent</th>
                                <th>Actions</th>
                                <th>Risque</th>
                                <th>Durée</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($interactions as $interaction)
                                @php
                                    $riskColor = $interaction->risk_score >= 85 ? 'var(--accent-red)' : ($interaction->risk_score >= 60 ? 'var(--accent-yellow)' : 'var(--accent-green)');
                                @endphp
                                <tr>
                                    <td><span class="ip-addr">{{ $interaction->source_ip }}</span></td>
                                    <td class="text-muted-small">{{ $interaction->city }}, {{ $interaction->country }}</td>
                                    <td>
                                        @if($interaction->credentials_attempted)
                                            <div class="hp-creds-box" style="margin-top: 0; padding: 0.45rem 0.6rem;">
                                                <span>👤</span>
                                                <span class="hp-creds-user">{{ $interaction->credentials_attempted['username'] ?? '?' }}</span>
                                                <span class="text-muted-small">:</span>
                                                <span class="hp-creds-pass">{{ $interaction->credentials_attempted['password'] ?? '?' }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted-small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted-small" title="{{ $interaction->user_agent }}">{{ Str::limit($interaction->user_agent, 28) }}</td>
                                    <td class="text-muted-small">
                                        @if($interaction->actions_taken)
                                            {{ collect($interaction->actions_taken)->take(2)->join(' · ') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color: {{ $riskColor }};">{{ $interaction->risk_score }}</strong>
                                    </td>
                                    <td class="mono text-muted-small">{{ $interaction->session_duration }}s</td>
                                    <td class="text-muted-small">{{ $interaction->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">🕸️</div>
                                            <p class="empty-state-title">Aucune interaction enregistrée</p>
                                            <p class="empty-state-text">Ce piège n’a pas encore reçu de tentative exploitable.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrap">
                    {{ $interactions->links() }}
                </div>
            </section>
        </div>

        <div class="honeypot-detail-side">
            <section class="card dashboard-panel">
                <div class="section-header">
                    <div class="section-title">Actions</div>
                </div>

                <div class="action-grid">
                    <button class="btn btn-warning btn-center" onclick="simulateInteraction()">
                        Simuler une interaction
                    </button>
                    <a href="{{ route('honeypot.index') }}" class="btn btn-primary btn-center">
                        Retour honeypot
                    </a>
                </div>
            </section>

            @if($trap->lure_content)
                <section class="honeypot-lure-shell">
                    <div class="section-title section-title--spaced">Contenu appât</div>
                    <div class="honeypot-lure-content">{{ json_encode(json_decode($trap->lure_content), JSON_PRETTY_PRINT) }}</div>
                </section>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
async function simulateInteraction() {
    try {
        const response = await csrfFetch('/honeypot/simulate/{{ $trap->id }}', { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            const interaction = data.interaction;
            showToast(`Interaction simulée depuis ${interaction.ip} (${interaction.country}).`, 'warning');
            window.setTimeout(() => window.location.reload(), 1200);
        }
    } catch (error) {
        showToast('La simulation d’interaction a échoué.', 'error');
    }
}
</script>
@endpush
