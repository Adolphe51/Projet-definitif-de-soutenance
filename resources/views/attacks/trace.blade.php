@extends('layouts.app')
@section('title', 'Traçage — ' . $ip)
@section('page-title', '🎯 Traçage Attaquant')

@section('content')
<div style="display:grid; grid-template-columns:1fr 340px; gap:20px;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:20px;">
                <div style="font-size:40px;">🎯</div>
                <div>
                    <div style="font-family:'Rajdhani',sans-serif; font-size:26px; font-weight:700;">Tracé: <span class="ip-addr" style="font-size:22px;">{{ $ip }}</span></div>
                    <div style="font-size:12px; color:var(--text-muted);">Analyse et traçage en temps réel</div>
                </div>
            </div>

            <!-- Traceroute simulé -->
            <div style="font-family:'Share Tech Mono',monospace; font-size:12px; background:var(--bg-primary); border-radius:8px; padding:16px; margin-bottom:16px;">
                <div style="color:var(--accent-cyan); margin-bottom:8px;">$ traceroute {{ $ip }}</div>
                <div id="trace-output" style="color:var(--accent-green); line-height:2;"></div>
            </div>

            <!-- Résultats géo -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                @foreach(['Pays' => $geo['country'], 'Ville' => $geo['city'], 'Latitude' => $geo['lat'], 'Longitude' => $geo['lon'], 'ISP' => $geo['isp'], 'ASN' => 'AS' . rand(10000,65000)] as $k => $v)
                <div style="background:var(--bg-secondary); border-radius:8px; padding:12px;">
                    <div style="font-family:'Share Tech Mono',monospace; font-size:10px; color:var(--text-muted); margin-bottom:4px;">{{ $k }}</div>
                    <div style="font-weight:700; font-size:14px;">{{ $v }}</div>
                </div>
                @endforeach
            </div>
        </div>

        @if($attack)
        <div class="card">
            <div class="section-title" style="margin-bottom:14px;">Dernière Attaque de cet IP</div>
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:32px;">{{ $attack->type_icon }}</span>
                <div>
                    <div style="font-weight:700;">{{ $attack->type }}</div>
                    <div style="font-size:12px; color:var(--text-muted);">{{ $attack->created_at->diffForHumans() }}</div>
                </div>
                <span class="badge badge-{{ $attack->severity }}" style="margin-left:auto;">{{ $attack->severity }}</span>
                <a href="{{ route('attacks.show', $attack->id) }}" class="btn btn-primary btn-sm">Voir détails</a>
            </div>
        </div>
        @endif
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="section-title" style="margin-bottom:14px;">Actions</div>
            <div style="display:grid; gap:8px;">
                <button class="btn btn-danger" style="justify-content:center;" onclick="blockIP('{{ $ip }}')">
                    <i class="fas fa-ban"></i> Bloquer {{ $ip }}
                </button>
                <button class="btn btn-warning" style="justify-content:center;" onclick="triggerAlarm('high')">
                    <i class="fas fa-exclamation-triangle"></i> Déclencher Alarme
                </button>
                <a href="{{ route('attacks.index') }}" class="btn btn-primary" style="justify-content:center;">
                    <i class="fas fa-list"></i> Voir toutes attaques
                </a>
            </div>
        </div>
        <div class="card" style="background:rgba(255,0,64,0.05); border-color:rgba(255,0,64,0.2);">
            <div style="font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--accent-red); margin-bottom:10px;">⚠ AVERTISSEMENT</div>
            <div style="font-size:12px; color:var(--text-muted); line-height:1.7;">
                Ces données de géolocalisation sont fournies à des fins de cybersécurité défensive uniquement. L'utilisation de ces informations à des fins offensives ou illégales est strictement interdite.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const hops = [
    '192.168.1.1',
    '10.{{ rand(0,255) }}.{{ rand(0,255) }}.1',
    '{{ preg_replace('/\.\d+$/', '.1', $ip) }}',
    '{{ preg_replace('/\.\d+$/', '.254', $ip) }}',
    '{{ $ip }}'
];

let i = 0;
const out = document.getElementById('trace-output');
function addHop() {
    if (i >= hops.length) return;
    const ms   = (Math.random() * 50 + 5).toFixed(1);
    const line = document.createElement('div');
    line.textContent = ` ${i+1}  ${hops[i].padEnd(18)}  ${ms} ms`;
    out.appendChild(line);
    i++;
    setTimeout(addHop, 600);
}
setTimeout(addHop, 500);

async function blockIP(ip) {
    showToast('🛡️ Blocage', `IP ${ip} en cours de blocage...`, 'medium');
    setTimeout(() => showToast('✅ Bloqué', `IP ${ip} bloquée avec succès`, 'low'), 1500);
}
</script>
@endpush
