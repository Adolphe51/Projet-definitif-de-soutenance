@extends('layouts.app')
@section('title', 'Alertes — CyberGuard')
@section('page-title', '🔔 Centre d\'Alertes')

@push('styles')
<style>
.alert-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: all 0.2s;
    border-left: 3px solid transparent;
    position: relative;
}
.alert-card.unread { background: var(--bg-card2); }
.alert-card.unread::after {
    content: 'NOUVEAU';
    position: absolute; top: 10px; right: 12px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 9px; color: var(--accent-red);
    background: rgba(255,0,64,0.1);
    padding: 2px 6px; border-radius: 3px;
    border: 1px solid rgba(255,0,64,0.3);
}
.alert-card.sev-critical { border-left-color: var(--critical); }
.alert-card.sev-high     { border-left-color: var(--high); }
.alert-card.sev-medium   { border-left-color: var(--medium); }
.alert-card.sev-low      { border-left-color: var(--low); }
.alert-icon { font-size: 24px; flex-shrink: 0; margin-top: 2px; }
.alert-body { flex: 1; }
.alert-title { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.alert-msg   { font-size: 12px; color: var(--text-muted); margin-bottom: 6px; }
.alert-meta  { font-family: 'Share Tech Mono', monospace; font-size: 10px; color: var(--text-muted); display: flex; gap: 12px; }
.alert-actions { display: flex; gap: 6px; align-items: flex-start; flex-shrink: 0; }
.sound-btn {
    background: rgba(255,214,0,0.1); border: 1px solid rgba(255,214,0,0.3);
    color: var(--accent-yellow); border-radius: 6px; padding: 5px 10px;
    cursor: pointer; font-size: 12px; transition: all 0.2s;
}
.sound-btn:hover { background: rgba(255,214,0,0.2); }
</style>
@endpush

@section('content')

<!-- Stats rapides -->
<div style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
    @php $unread = $alerts->where('acknowledged', false)->count(); @endphp
    <div class="stat-card" style="--accent-color:var(--accent-red); padding:14px 20px;">
        <div class="stat-value" style="font-size:28px;">{{ $unread }}</div>
        <div class="stat-label">Non lues</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--accent-yellow); padding:14px 20px;">
        <div class="stat-value" style="font-size:28px;">{{ $alerts->where('severity','critical')->count() }}</div>
        <div class="stat-label">Critiques</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--accent-cyan); padding:14px 20px;">
        <div class="stat-value" style="font-size:28px;">{{ $alerts->total() }}</div>
        <div class="stat-label">Total</div>
    </div>

    <div style="margin-left:auto; display:flex; gap:8px; align-items:center;">
        <button class="btn btn-danger" onclick="triggerManualAlarm()">
            <i class="fas fa-volume-up"></i> Test Alarme
        </button>
        <button class="btn btn-primary" onclick="acknowledgeAll()">
            <i class="fas fa-check-double"></i> Tout Marquer Lu
        </button>
    </div>
</div>

<!-- Live alert indicator -->
<div id="live-alert-bar" style="
    display:flex; align-items:center; gap:10px; padding:10px 16px;
    background:rgba(0,229,255,0.05); border:1px solid rgba(0,229,255,0.15);
    border-radius:8px; margin-bottom:16px;
    font-family:'Share Tech Mono',monospace; font-size:12px; color:var(--text-muted);
">
    <div style="width:6px;height:6px;border-radius:50%;background:var(--accent-green);box-shadow:0 0 6px var(--accent-green);animation:blink 1s infinite;"></div>
    Écoute des nouvelles alertes en temps réel...
    <span id="new-alert-notif" style="color:var(--accent-red);"></span>
</div>

<!-- Alerts list -->
<div id="alerts-list-container">
    @forelse($alerts as $alert)
    <div class="alert-card sev-{{ $alert->severity }} {{ !$alert->acknowledged ? 'unread' : '' }}" id="alert-{{ $alert->id }}">
        <div class="alert-icon">
            {{ $alert->severity === 'critical' ? '💀' : ($alert->severity === 'high' ? '🔴' : ($alert->severity === 'medium' ? '⚠️' : '✅')) }}
        </div>
        <div class="alert-body">
            <div class="alert-title">{{ $alert->title }}</div>
            <div class="alert-msg">{{ $alert->message }}</div>
            <div class="alert-meta">
                <span>{{ $alert->created_at->diffForHumans() }}</span>
                <span class="badge badge-{{ $alert->severity }}">{{ $alert->severity }}</span>
                @if($alert->type)
                <span class="badge badge-info">{{ $alert->type }}</span>
                @endif
                @if($alert->attack_id)
                <a href="{{ route('attacks.show', $alert->attack_id) }}" style="color:var(--accent-cyan);text-decoration:none;">
                    Voir attaque #{{ $alert->attack_id }} →
                </a>
                @endif
            </div>
        </div>
        <div class="alert-actions">
            <button class="sound-btn" onclick="playAlertSound('{{ $alert->severity }}')" title="Rejouer alerte sonore">
                🔊
            </button>
            @if(!$alert->acknowledged)
            <button class="btn btn-success btn-sm" onclick="acknowledgeAlert({{ $alert->id }}, this)">
                <i class="fas fa-check"></i>
            </button>
            @else
            <span style="color:var(--text-muted); font-size:20px;">✓</span>
            @endif
        </div>
    </div>
    @empty
    <div style="text-align:center; padding:80px 20px; color:var(--text-muted);">
        <div style="font-size:64px; margin-bottom:16px;">🔕</div>
        <div style="font-size:18px; margin-bottom:8px;">Aucune alerte</div>
        <div style="font-size:13px;">Le système est calme. Toutes les alertes ont été traitées.</div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div style="display:flex; justify-content:center; margin-top:20px; gap:6px;">
    {{ $alerts->links() }}
</div>
@endsection

@push('scripts')
<script>
async function acknowledgeAlert(id, btn) {
    btn.disabled = true;
    await csrfFetch(`/alerts/acknowledge/${id}`, { method: 'POST' });
    const card = document.getElementById(`alert-${id}`);
    card.classList.remove('unread');
    const newTag = card.querySelector('[style*="NOUVEAU"]');
    if (newTag) newTag.remove();
    btn.outerHTML = '<span style="color:var(--text-muted);font-size:20px;">✓</span>';
    updateAlertCount();
}

async function acknowledgeAll() {
    await csrfFetch('/alerts/clear-all', { method: 'POST' });
    document.querySelectorAll('.alert-card.unread').forEach(c => {
        c.classList.remove('unread');
    });
    showToast('✅ Toutes lues', 'Toutes les alertes ont été marquées comme lues.', 'low');
    updateAlertCount();
}

function updateAlertCount() {
    const remaining = document.querySelectorAll('.alert-card.unread').length;
    document.getElementById('topbar-alert-count').textContent = remaining;
    document.getElementById('nav-alert-count').textContent    = remaining;
}

function playAlertSound(severity) {
    initAudio();
    const freq = severity === 'critical' ? 880 : severity === 'high' ? 660 : 440;
    playBeep(freq, 0.3, 'sawtooth', 0.3);
    setTimeout(() => playBeep(freq * 1.2, 0.2, 'sawtooth', 0.2), 350);
}

// Polling nouvelles alertes
let lastAlertId = {{ $alerts->first()?->id ?? 0 }};
setInterval(async () => {
    try {
        const res  = await fetch('/alerts/unread');
        const data = await res.json();
        if (data.count > 0 && data.alerts[0]?.id > lastAlertId) {
            lastAlertId = data.alerts[0].id;
            document.getElementById('new-alert-notif').textContent = `⚡ ${data.count} nouvelle(s) alerte(s) — `;
            const link = document.createElement('a');
            link.href = '/alerts'; link.style.color = 'var(--accent-cyan)'; link.textContent = 'Actualiser';
            document.getElementById('new-alert-notif').appendChild(link);
        }
    } catch(e) {}
}, 6000);
</script>
@endpush
