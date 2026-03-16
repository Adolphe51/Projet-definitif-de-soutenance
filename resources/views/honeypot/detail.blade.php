@extends('layouts.app')
@section('title', 'Piège: ' . $trap->name)
@section('page-title', '🍯 Détail Piège: ' . $trap->name)

@section('content')
<div style="display:grid; grid-template-columns:1fr 320px; gap:20px;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex; gap:16px; align-items:center; margin-bottom:16px;">
                <div style="font-size:48px;">🍯</div>
                <div>
                    <div style="font-family:'Rajdhani',sans-serif; font-size:26px; font-weight:700;">{{ $trap->name }}</div>
                    <div style="font-size:12px; color:var(--text-muted);">{{ $trap->description }}</div>
                    <div style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap;">
                        @if($trap->fake_service)<span class="badge badge-info">{{ $trap->fake_service }}</span>@endif
                        @if($trap->port)<span class="badge" style="background:rgba(168,85,247,0.1);color:#a855f7;border-color:#a855f7;">:{{ $trap->port }}</span>@endif
                        @if($trap->path)<span class="mono" style="font-size:11px;color:var(--text-muted);">{{ $trap->path }}</span>@endif
                    </div>
                </div>
                <div style="margin-left:auto; text-align:center;">
                    <div style="font-family:'Rajdhani',sans-serif; font-size:42px; font-weight:700; color:var(--accent-red); line-height:1;">
                        {{ $trap->interactions_count }}
                    </div>
                    <div style="font-size:11px; color:var(--text-muted);">Interactions</div>
                </div>
            </div>
        </div>

        <!-- Interactions table -->
        <div class="card">
            <div class="section-title" style="margin-bottom:16px;">Toutes les Interactions</div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>IP Source</th>
                            <th>Localisation</th>
                            <th>Credentials Testés</th>
                            <th>User-Agent</th>
                            <th>Actions</th>
                            <th>Risque</th>
                            <th>Durée</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interactions as $i)
                        <tr>
                            <td><span class="ip-addr">{{ $i->source_ip }}</span></td>
                            <td style="font-size:12px;">🌍 {{ $i->city }}, {{ $i->country }}</td>
                            <td>
                                @if($i->credentials_attempted)
                                <div style="font-family:'Share Tech Mono',monospace; font-size:11px;">
                                    <span style="color:var(--accent-cyan);">{{ $i->credentials_attempted['username'] ?? '?' }}</span>
                                    <span style="color:var(--text-muted);">:</span>
                                    <span style="color:var(--accent-red);">{{ $i->credentials_attempted['password'] ?? '?' }}</span>
                                </div>
                                @else
                                <span style="color:var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="font-size:11px; color:var(--text-muted); max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $i->user_agent }}">{{ Str::limit($i->user_agent, 20) }}</td>
                            <td style="font-size:11px;">
                                @if($i->actions_taken)
                                    @foreach(array_slice($i->actions_taken, 0, 2) as $action)
                                    <div style="color:var(--text-muted);">• {{ $action }}</div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                <div style="font-family:'Rajdhani',sans-serif; font-size:18px; font-weight:700;
                                    color:{{ $i->risk_score >= 85 ? 'var(--accent-red)' : ($i->risk_score >= 60 ? 'var(--accent-yellow)' : 'var(--accent-green)') }};">
                                    {{ $i->risk_score }}
                                </div>
                            </td>
                            <td class="mono" style="font-size:12px;">{{ $i->session_duration }}s</td>
                            <td style="font-size:11px; color:var(--text-muted);">{{ $i->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:12px;">{{ $interactions->links() }}</div>
        </div>
    </div>

    <!-- Side panel -->
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="section-title" style="margin-bottom:14px;">Actions</div>
            <div style="display:grid; gap:8px;">
                <button class="btn btn-warning" style="justify-content:center;" onclick="sim()">
                    <i class="fas fa-bolt"></i> Simuler Interaction
                </button>
                <a href="{{ route('honeypot.index') }}" class="btn btn-primary" style="justify-content:center;">
                    <i class="fas fa-arrow-left"></i> Retour Honeypot
                </a>
            </div>
        </div>

        <!-- Lure content -->
        @if($trap->lure_content)
        <div class="card">
            <div class="section-title" style="margin-bottom:12px;">🎣 Contenu Appât</div>
            <div style="background:var(--bg-primary); border:1px solid rgba(255,214,0,0.2); border-radius:8px; padding:12px; font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--accent-yellow); max-height:200px; overflow-y:auto; white-space:pre-wrap; word-break:break-all;">{{ json_encode(json_decode($trap->lure_content), JSON_PRETTY_PRINT) }}</div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
async function sim() {
    const res  = await csrfFetch('/honeypot/simulate/{{ $trap->id }}', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        const i = data.interaction;
        showToast('🍯 Interaction simulée', `IP: ${i.ip} (${i.country}) — Risk: ${i.risk_score}/100`, 'high');
        setTimeout(() => location.reload(), 2000);
    }
}
</script>
@endpush
