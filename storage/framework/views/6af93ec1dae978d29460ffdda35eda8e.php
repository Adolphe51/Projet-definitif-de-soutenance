<?php $__env->startSection('title', 'Détection Live — CyberGuard'); ?>
<?php $__env->startSection('page-title', '📡 Détection en Temps Réel'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.live-grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; }

.attack-stream {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.attack-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    margin-bottom: 8px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: all 0.3s;
    cursor: pointer;
    animation: rowAppear 0.4s ease-out;
}

@keyframes rowAppear {
    from { opacity: 0; transform: translateX(-20px); }
    to   { opacity: 1; transform: translateX(0); }
}

.attack-row:hover { border-color: var(--border-glow); transform: translateX(3px); }
.attack-row.critical { border-left: 3px solid var(--critical); animation: rowAppear 0.4s ease-out, rowFlash 0.5s ease-in-out 3; }
.attack-row.high     { border-left: 3px solid var(--high); }
.attack-row.medium   { border-left: 3px solid var(--medium); }
.attack-row.low      { border-left: 3px solid var(--low); }

@keyframes rowFlash {
    0%,100% { background: var(--bg-card); }
    50%      { background: rgba(255,0,64,0.08); }
}

.attack-type-icon { font-size: 24px; flex-shrink: 0; }

.attack-info { flex: 1; min-width: 0; }
.attack-title { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.attack-meta  { font-family: 'Share Tech Mono', monospace; font-size: 11px; color: var(--text-muted); display: flex; gap: 10px; flex-wrap: wrap; }

.attack-stats { text-align: right; flex-shrink: 0; }
.attack-stats .packets { font-family: 'Rajdhani', sans-serif; font-size: 20px; font-weight: 700; color: var(--accent-cyan); }
.attack-stats .bw      { font-size: 11px; color: var(--text-muted); }

.side-panel { display: flex; flex-direction: column; gap: 16px; }

.mini-map-placeholder {
    height: 200px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.radar {
    width: 160px; height: 160px;
    border-radius: 50%;
    border: 2px solid rgba(0,229,255,0.3);
    position: relative;
    display: flex; align-items: center; justify-content: center;
}

.radar::before, .radar::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(0,229,255,0.2);
}
.radar::before { width: 70%; height: 70%; }
.radar::after  { width: 40%; height: 40%; }

.radar-sweep {
    position: absolute;
    width: 50%; height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent-cyan));
    transform-origin: left center;
    top: 50%; left: 50%;
    animation: sweep 2s linear infinite;
}

@keyframes sweep { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.radar-dot {
    position: absolute;
    width: 6px; height: 6px;
    border-radius: 50%;
    animation: dotPulse 1s ease-in-out infinite;
}

@keyframes dotPulse { 0%,100%{transform:scale(1); opacity:1;} 50%{transform:scale(2); opacity:0.5;} }

.counter-box {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px;
}

.counter-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(26,58,92,0.4);
    font-size: 13px;
}

.counter-row:last-child { border-bottom: none; }
.counter-val { font-family: 'Rajdhani', sans-serif; font-size: 22px; font-weight: 700; }

.live-indicator {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px;
    background: rgba(255,0,64,0.1);
    border: 1px solid rgba(255,0,64,0.3);
    border-radius: 6px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: var(--accent-red);
    margin-bottom: 16px;
}

.live-dot {
    width: 8px; height: 8px;
    background: var(--accent-red);
    border-radius: 50%;
    animation: blink 0.8s ease-in-out infinite;
    box-shadow: 0 0 8px var(--accent-red);
}

.filter-bar {
    display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;
}

.filter-btn {
    padding: 5px 12px;
    border-radius: 5px;
    border: 1px solid var(--border);
    background: var(--bg-card);
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn.active, .filter-btn:hover {
    border-color: var(--accent-cyan);
    color: var(--accent-cyan);
    background: rgba(0,229,255,0.08);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="live-indicator">
    <div class="live-dot"></div>
    SURVEILLANCE EN DIRECT — Mise à jour toutes les 3 secondes
    <span id="attack-counter-display" style="margin-left: auto; color: var(--accent-cyan);">0 attaques reçues</span>
</div>

<div class="live-grid">
    <!-- Stream principal -->
    <div>
        <div class="section-header">
            <div class="section-title">Flux d'Attaques</div>
            <div class="filter-bar">
                <button class="filter-btn active" onclick="filterSeverity('all', this)">Toutes</button>
                <button class="filter-btn" onclick="filterSeverity('critical', this)" style="color:var(--critical)">Critiques</button>
                <button class="filter-btn" onclick="filterSeverity('high', this)" style="color:var(--high)">Élevées</button>
                <button class="filter-btn" onclick="filterSeverity('medium', this)" style="color:var(--medium)">Moyennes</button>
                <button class="filter-btn" onclick="filterSeverity('low', this)" style="color:var(--low)">Faibles</button>
            </div>
        </div>

        <div class="attack-stream" id="attack-stream">
            <div style="text-align:center; padding:60px 20px; color:var(--text-muted);">
                <div style="font-size:48px; margin-bottom:16px; animation: blink 1s infinite;">📡</div>
                <div style="font-family:'Share Tech Mono',monospace;">En attente d'attaques...</div>
            </div>
        </div>
    </div>

    <!-- Panneau latéral -->
    <div class="side-panel">
        <!-- Radar -->
        <div class="mini-map-placeholder">
            <div class="radar">
                <div class="radar-sweep"></div>
                <div id="radar-dots"></div>
                <div style="font-family:'Share Tech Mono',monospace; font-size:12px; color:var(--accent-cyan); z-index:1;">RADAR</div>
            </div>
        </div>

        <!-- Compteurs -->
        <div class="counter-box">
            <div style="font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--text-muted); margin-bottom:8px; letter-spacing:1px;">COMPTEURS EN DIRECT</div>
            <div class="counter-row">
                <span>💀 Total</span>
                <span class="counter-val" id="c-total" style="color:var(--accent-cyan);">0</span>
            </div>
            <div class="counter-row">
                <span>🔴 Critiques</span>
                <span class="counter-val" id="c-critical" style="color:var(--critical);">0</span>
            </div>
            <div class="counter-row">
                <span>🟡 Élevées</span>
                <span class="counter-val" id="c-high" style="color:var(--high);">0</span>
            </div>
            <div class="counter-row">
                <span>⚡ Paquets/s</span>
                <span class="counter-val" id="c-packets" style="color:var(--accent-green);">0</span>
            </div>
        </div>

        <!-- Dernière attaque détectée -->
        <div class="card" id="last-attack-card">
            <div style="font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--text-muted); margin-bottom:12px;">DERNIÈRE ATTAQUE</div>
            <div id="last-attack-content" style="color:var(--text-muted); font-size:13px;">Aucune attaque reçue</div>
        </div>

        <!-- Bouton bloquer -->
        <button class="btn btn-danger" style="justify-content:center; width:100%;" onclick="blockLastAttacker()">
            <i class="fas fa-ban"></i> Bloquer Dernier Attaquant
        </button>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let attackCount   = 0;
let critCount     = 0;
let highCount     = 0;
let lastAttackId  = null;
let currentFilter = 'all';
let allAttacks    = [];

async function fetchLiveAttacks() {
    try {
        const res  = await fetch('/api/live-attacks');
        const data = await res.json();

        attackCount = data.total;
        critCount   = data.critical;
        document.getElementById('attack-counter-display').textContent = `${attackCount} attaques`;
        document.getElementById('c-total').textContent    = attackCount;
        document.getElementById('c-critical').textContent = critCount;

        allAttacks = data.attacks;
        renderAttacks(allAttacks);
        updateRadar(data.attacks.slice(0, 8));

        if (data.new_attack) {
            const a = data.new_attack;
            if (a.alarm && (a.severity === 'critical' || a.severity === 'high')) {
                if (!alarmActive) {
                    triggerAlarm(a.severity);
                    showToast(`${a.severity === 'critical' ? '💀' : '🔴'} ${a.type}`, `${a.ip} → ${a.country}`, a.severity);
                }
            } else {
                showToast(`⚡ ${a.type}`, `Source: ${a.ip} (${a.country})`, a.severity, 4000);
            }
        }

        // Compteur de paquets simulé
        document.getElementById('c-packets').textContent = (Math.floor(Math.random() * 5000) + 500).toLocaleString();

    } catch (e) { console.error(e); }
}

function renderAttacks(attacks) {
    const filtered = currentFilter === 'all' ? attacks : attacks.filter(a => a.severity === currentFilter);
    const stream   = document.getElementById('attack-stream');

    if (filtered.length === 0) {
        stream.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);">Aucune attaque correspondante</div>';
        return;
    }

    stream.innerHTML = filtered.map(a => `
        <div class="attack-row ${a.severity}" onclick="viewAttack(${a.id})">
            <div class="attack-type-icon">${a.icon}</div>
            <div class="attack-info">
                <div class="attack-title">
                    ${a.type}
                    <span class="badge badge-${a.severity}" style="margin-left:6px;">${a.severity}</span>
                    ${a.is_simulation ? '<span class="badge" style="background:rgba(168,85,247,0.15);color:#a855f7;border-color:#a855f7;margin-left:4px;">SIM</span>' : ''}
                    ${a.status === 'blocked' ? '<span class="badge badge-low" style="margin-left:4px;">BLOQUÉ</span>' : ''}
                </div>
                <div class="attack-meta">
                    <span class="ip-addr">${a.source_ip}</span>
                    <span>🌍 ${a.city}, ${a.country}</span>
                    <span>🔌 Port ${a.target_ip}</span>
                    <span>⏱ ${a.time}</span>
                </div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">${a.description || ''}</div>
            </div>
            <div class="attack-stats">
                <div class="packets">${Number(a.packet_count || 0).toLocaleString()}</div>
                <div class="bw">${a.bandwidth_mbps || 0} Mbps</div>
                <div style="margin-top:4px;">
                    <button class="btn btn-danger btn-sm" onclick="event.stopPropagation(); blockAttack(${a.id})">
                        <i class="fas fa-ban"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    // Mettre à jour carte dernière attaque
    const last = filtered[0];
    if (last) {
        lastAttackId = last.id;
        document.getElementById('last-attack-content').innerHTML = `
            <div style="font-size:20px; margin-bottom:8px;">${last.icon} ${last.type}</div>
            <div class="ip-addr" style="margin-bottom:6px;">${last.source_ip}</div>
            <div style="color:var(--text-muted); font-size:12px; margin-bottom:4px;">🌍 ${last.city}, ${last.country}</div>
            <div class="badge badge-${last.severity}" style="margin-top:4px;">${last.severity}</div>
        `;
    }
}

function updateRadar(attacks) {
    const container = document.getElementById('radar-dots');
    container.innerHTML = attacks.map((a, i) => {
        const angle  = (i / attacks.length) * 360;
        const radius = 30 + Math.random() * 35;
        const x = 80 + radius * Math.cos((angle * Math.PI) / 180);
        const y = 80 + radius * Math.sin((angle * Math.PI) / 180);
        const color = { critical: '#ff0040', high: '#ff6b00', medium: '#ffd600', low: '#00ff88' }[a.severity] || '#00e5ff';
        return `<div class="radar-dot" style="left:${x-3}px;top:${y-3}px;background:${color};box-shadow:0 0 6px ${color};"></div>`;
    }).join('');
}

function filterSeverity(sev, btn) {
    currentFilter = sev;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderAttacks(allAttacks);
}

async function blockAttack(id) {
    await csrfFetch(`/attacks/block/${id}`, { method: 'POST' });
    showToast('🛡️ IP Bloquée', 'Attaquant bloqué avec succès.', 'low');
    fetchLiveAttacks();
}

function blockLastAttacker() {
    if (lastAttackId) blockAttack(lastAttackId);
}

function viewAttack(id) {
    window.location.href = `/attacks/${id}`;
}

// Polling
fetchLiveAttacks();
setInterval(fetchLiveAttacks, 3000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hp\Desktop\cyberguard\cyberguard\resources\views/attacks/live.blade.php ENDPATH**/ ?>