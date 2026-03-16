<?php $__env->startSection('title', 'Honeypot — CyberGuard'); ?>
<?php $__env->startSection('page-title', '🍯 Environnement Honeypot'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.hp-grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; }
.trap-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 12px;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}
.trap-card:hover { border-color: var(--border-glow); }
.trap-card.active::before {
    content: '';
    position: absolute; top: 0; left: 0;
    width: 3px; height: 100%;
    background: var(--accent-green);
}
.trap-card.triggered::before {
    content: '';
    position: absolute; top: 0; left: 0;
    width: 3px; height: 100%;
    background: var(--accent-red);
    animation: borderFlow 0.8s ease-in-out infinite;
}
.trap-card.inactive::before {
    content: '';
    position: absolute; top: 0; left: 0;
    width: 3px; height: 100%;
    background: var(--text-muted);
}
.trap-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.trap-info { flex: 1; }
.trap-name { font-size: 15px; font-weight: 700; margin-bottom: 3px; }
.trap-desc { font-size: 12px; color: var(--text-muted); }
.trap-stats { display: flex; gap: 12px; margin-top: 10px; font-size: 12px; }
.trap-stat { display: flex; flex-direction: column; align-items: center; padding: 6px 10px; background: var(--bg-secondary); border-radius: 6px; }
.trap-stat-val { font-family: 'Rajdhani', sans-serif; font-size: 20px; font-weight: 700; color: var(--accent-cyan); line-height: 1; }
.trap-stat-lbl { font-size: 10px; color: var(--text-muted); margin-top: 2px; }
.trap-actions { display: flex; gap: 6px; margin-top: 10px; }

.interaction-feed {
    max-height: calc(100vh - 280px);
    overflow-y: auto;
}
.interaction-item {
    background: var(--bg-card2);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    animation: rowAppear 0.4s ease-out;
}
.interaction-item.high-risk { border-left: 3px solid var(--accent-red); }
.interaction-item.med-risk  { border-left: 3px solid var(--accent-yellow); }

.risk-bar {
    height: 4px;
    background: var(--bg-secondary);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 6px;
}
.risk-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.5s;
}

.trap-type-icons {
    'fake_login': '🔐', 'fake_admin': '⚙️', 'fake_db': '🗄️',
    'fake_api': '🔌', 'fake_ssh': '💻', 'fake_ftp': '📁',
    'fake_phpmyadmin': '🐬', 'fake_wordpress': '📝',
    'canary_token': '🐤', 'fake_document': '📄'
}

.creds-box {
    background: var(--bg-primary);
    border: 1px solid rgba(255,0,64,0.2);
    border-radius: 6px;
    padding: 8px 10px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    margin-top: 6px;
    display: flex; gap: 10px;
}
.creds-user { color: var(--accent-cyan); }
.creds-pass { color: var(--accent-red); }

.honey-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 4px;
    font-family: 'Share Tech Mono', monospace; font-size: 11px;
}
.honey-active    { background: rgba(0,255,136,0.1);  color: var(--accent-green); border: 1px solid rgba(0,255,136,0.3); }
.honey-triggered { background: rgba(255,0,64,0.12);  color: var(--accent-red);   border: 1px solid rgba(255,0,64,0.3); animation: badgePulse 1s infinite; }
.honey-inactive  { background: rgba(74,122,155,0.1); color: var(--text-muted);   border: 1px solid var(--border); }

.network-map {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

/* Fake server terminal */
.fake-terminal {
    background: #000;
    border: 1px solid rgba(0,255,136,0.3);
    border-radius: 8px;
    padding: 14px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    line-height: 1.8;
    max-height: 180px;
    overflow-y: auto;
    margin-top: 12px;
}
.t-green  { color: #00ff88; }
.t-red    { color: #ff0040; }
.t-yellow { color: #ffd600; }
.t-cyan   { color: #00e5ff; }
.t-gray   { color: #555; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<!-- Bannière honeypot -->
<div style="
    background: linear-gradient(135deg, rgba(255,214,0,0.08), rgba(255,107,0,0.05));
    border: 1px solid rgba(255,214,0,0.25);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex; align-items: center; gap: 14px;
">
    <div style="font-size: 36px;">🍯</div>
    <div>
        <div style="font-family:'Rajdhani',sans-serif; font-size:18px; font-weight:700; color:var(--accent-yellow);">
            ENVIRONNEMENT HONEYPOT ACTIF
        </div>
        <div style="font-size:12px; color:var(--text-muted);">
            Pièges déployés pour tromper et tracer les attaquants. Toutes les interactions sont enregistrées, analysées et transformées en alertes en temps réel.
        </div>
    </div>
    <div style="margin-left:auto; display:flex; gap:8px;">
        <button class="btn btn-warning btn-sm" onclick="initializeTraps()">
            <i class="fas fa-plus"></i> Init Pièges
        </button>
        <button class="btn btn-primary btn-sm" onclick="simulateAll()">
            <i class="fas fa-play"></i> Simuler Attaque
        </button>
    </div>
</div>

<!-- Stats row -->
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card" style="--accent-color:var(--accent-yellow);">
        <div class="stat-value" id="hp-total"><?php echo e($totalInteractions); ?></div>
        <div class="stat-label">Interactions</div>
        <div class="stat-icon">🍯</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--accent-cyan);">
        <div class="stat-value" id="hp-unique"><?php echo e($uniqueAttackers); ?></div>
        <div class="stat-label">Attaquants Uniques</div>
        <div class="stat-icon">👤</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--accent-red);">
        <div class="stat-value" id="hp-creds"><?php echo e($credsCaptured); ?></div>
        <div class="stat-label">Credentials Capturés</div>
        <div class="stat-icon">🔑</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--accent-green);">
        <div class="stat-value"><?php echo e($traps->where('status','active')->count()); ?></div>
        <div class="stat-label">Pièges Actifs</div>
        <div class="stat-icon">✅</div>
    </div>
</div>

<div class="hp-grid">
    <!-- Pièges déployés -->
    <div>
        <div class="section-header">
            <div class="section-title">Pièges Déployés</div>
            <span style="font-size:12px; color:var(--text-muted);"><?php echo e($traps->count()); ?> pièges</span>
        </div>

        <?php
        $trapIcons = [
            'fake_login'    => '🔐', 'fake_admin'   => '⚙️',
            'fake_db'       => '🗄️', 'fake_api'     => '🔌',
            'fake_ssh'      => '💻', 'fake_ftp'     => '📁',
            'fake_phpmyadmin' => '🐬', 'fake_wordpress' => '📝',
            'canary_token'  => '🐤', 'fake_document' => '📄',
        ];
        $trapColors = [
            'fake_login' => 'rgba(0,229,255,0.1)', 'fake_admin' => 'rgba(255,107,0,0.1)',
            'fake_db' => 'rgba(168,85,247,0.1)', 'fake_api' => 'rgba(0,255,136,0.1)',
            'fake_ssh' => 'rgba(255,214,0,0.1)', 'fake_ftp' => 'rgba(59,130,246,0.1)',
            'fake_phpmyadmin' => 'rgba(236,72,153,0.1)', 'fake_wordpress' => 'rgba(255,107,0,0.1)',
            'canary_token' => 'rgba(255,214,0,0.1)', 'fake_document' => 'rgba(0,229,255,0.1)',
        ];
        ?>

        <?php $__empty_1 = true; $__currentLoopData = $traps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="trap-card <?php echo e($trap->status); ?>" id="trap-<?php echo e($trap->id); ?>">
            <div style="display:flex; gap:14px;">
                <div class="trap-icon" style="background:<?php echo e($trapColors[$trap->type] ?? 'rgba(0,229,255,0.1)'); ?>;">
                    <?php echo e($trapIcons[$trap->type] ?? '🎣'); ?>

                </div>
                <div class="trap-info">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <div class="trap-name"><?php echo e($trap->name); ?></div>
                        <span class="honey-badge honey-<?php echo e($trap->status); ?>">
                            <?php if($trap->status === 'active'): ?> ● ACTIF
                            <?php elseif($trap->status === 'triggered'): ?> ⚡ DÉCLENCHÉ
                            <?php else: ?> ○ INACTIF
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="trap-desc"><?php echo e($trap->description); ?></div>
                    <div style="display:flex; gap:8px; margin-top:6px; flex-wrap:wrap;">
                        <?php if($trap->fake_service): ?>
                        <span class="badge badge-info"><?php echo e($trap->fake_service); ?></span>
                        <?php endif; ?>
                        <?php if($trap->port): ?>
                        <span class="badge" style="background:rgba(168,85,247,0.1);color:#a855f7;border-color:#a855f7;">Port <?php echo e($trap->port); ?></span>
                        <?php endif; ?>
                        <?php if($trap->path): ?>
                        <span class="mono" style="font-size:11px; color:var(--text-muted);"><?php echo e($trap->path); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="trap-stats">
                <div class="trap-stat">
                    <div class="trap-stat-val" style="color:<?php echo e($trap->interactions_count > 0 ? 'var(--accent-red)' : 'var(--text-muted)'); ?>;">
                        <?php echo e($trap->interactions_count); ?>

                    </div>
                    <div class="trap-stat-lbl">Interactions</div>
                </div>
                <div class="trap-stat">
                    <div class="trap-stat-val" style="font-size:13px; color:var(--text-muted);">
                        <?php echo e($trap->last_triggered_at ? $trap->last_triggered_at->diffForHumans() : '—'); ?>

                    </div>
                    <div class="trap-stat-lbl">Dernière activité</div>
                </div>
            </div>

            <div class="trap-actions">
                <button class="btn btn-warning btn-sm" onclick="simulateTrap(<?php echo e($trap->id); ?>, '<?php echo e($trap->name); ?>')">
                    <i class="fas fa-bolt"></i> Simuler
                </button>
                <a href="<?php echo e(route('honeypot.detail', $trap->id)); ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-chart-bar"></i> Détails
                </a>
                <?php if($trap->path): ?>
                <a href="<?php echo e(route('honeypot.trap.view', $trap->type)); ?>" target="_blank" class="btn btn-sm"
                    style="background:rgba(168,85,247,0.1);color:#a855f7;border:1px solid #a855f7;">
                    <i class="fas fa-external-link-alt"></i> Voir Piège
                </a>
                <?php endif; ?>
                <button class="btn btn-sm <?php echo e($trap->status === 'active' ? 'btn-danger' : 'btn-success'); ?>"
                    onclick="toggleTrap(<?php echo e($trap->id); ?>, this)" style="margin-left:auto;">
                    <?php echo e($trap->status === 'active' ? '⏸ Pause' : '▶ Activer'); ?>

                </button>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="text-align:center; padding:60px; color:var(--text-muted);">
            <div style="font-size:48px; margin-bottom:16px;">🍯</div>
            <div style="margin-bottom:12px;">Aucun piège configuré</div>
            <button class="btn btn-warning" onclick="initializeTraps()">
                <i class="fas fa-magic"></i> Initialiser les pièges
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Panneau droit: interactions + terminal -->
    <div>
        <!-- Interactions récentes -->
        <div class="card" style="margin-bottom:16px;">
            <div class="section-header">
                <div class="section-title">Interactions Récentes</div>
                <div style="width:8px;height:8px;border-radius:50%;background:var(--accent-red);box-shadow:0 0 8px var(--accent-red);animation:blink 0.8s infinite;"></div>
            </div>

            <div class="interaction-feed" id="interaction-feed">
                <?php $__empty_1 = true; $__currentLoopData = $interactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="interaction-item <?php echo e($interaction->risk_score >= 85 ? 'high-risk' : 'med-risk'); ?>">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                        <div style="flex:1;">
                            <div style="font-size:12px; font-weight:700;">
                                <?php echo e($interaction->trap->name ?? 'Piège inconnu'); ?>

                            </div>
                            <div class="mono" style="font-size:11px; color:var(--accent-cyan); margin-top:2px;">
                                <?php echo e($interaction->source_ip); ?>

                            </div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                🌍 <?php echo e($interaction->city); ?>, <?php echo e($interaction->country); ?>

                            </div>
                            <?php if($interaction->credentials_attempted): ?>
                            <div class="creds-box">
                                <span>👤</span>
                                <span class="creds-user"><?php echo e($interaction->credentials_attempted['username'] ?? '?'); ?></span>
                                <span style="color:var(--text-muted)">:</span>
                                <span class="creds-pass"><?php echo e($interaction->credentials_attempted['password'] ?? '?'); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700;
                                color:<?php echo e($interaction->risk_score >= 85 ? 'var(--accent-red)' : ($interaction->risk_score >= 60 ? 'var(--accent-yellow)' : 'var(--accent-green)')); ?>;">
                                <?php echo e($interaction->risk_score); ?>

                            </div>
                            <div style="font-size:10px; color:var(--text-muted);">RISQUE</div>
                            <div style="font-size:10px; color:var(--text-muted); margin-top:2px;"><?php echo e($interaction->created_at->diffForHumans()); ?></div>
                        </div>
                    </div>
                    <div class="risk-bar">
                        <div class="risk-fill" style="
                            width:<?php echo e($interaction->risk_score); ?>%;
                            background: <?php echo e($interaction->risk_score >= 85 ? 'var(--accent-red)' : ($interaction->risk_score >= 60 ? 'var(--accent-yellow)' : 'var(--accent-green)')); ?>;
                        "></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div style="text-align:center; padding:40px; color:var(--text-muted);">
                    <div style="font-size:32px; margin-bottom:8px;">🕸️</div>
                    En attente d'intrus...
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Terminal live -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div class="section-title">Terminal Honeypot</div>
                <div style="display:flex; gap:6px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:#ff5f56;"></div>
                    <div style="width:10px;height:10px;border-radius:50%;background:#ffbd2e;"></div>
                    <div style="width:10px;height:10px;border-radius:50%;background:#27c93f;"></div>
                </div>
            </div>
            <div class="fake-terminal" id="hp-terminal">
                <div class="t-cyan">honeypot@cyberguard:~$ <span class="t-green">service honeypot status</span></div>
                <div class="t-green">● honeypot.service - CyberGuard Honeypot Engine</div>
                <div class="t-gray">   Active: active (running) since startup</div>
                <div class="t-green">   Traps deployed: <?php echo e($traps->where('status','active')->count()); ?></div>
                <div class="t-cyan">honeypot@cyberguard:~$ <span class="t-yellow">tail -f /var/log/honeypot.log</span></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let lastInteractionId = <?php echo e($interactions->first()?->id ?? 0); ?>;
const terminal = document.getElementById('hp-terminal');

function addTerminalLine(text, cls = 't-green') {
    const ts  = new Date().toLocaleTimeString('fr-FR', { hour12: false });
    const div = document.createElement('div');
    div.className = cls;
    div.textContent = `[${ts}] ${text}`;
    terminal.appendChild(div);
    terminal.scrollTop = terminal.scrollHeight;
    // Limiter les lignes
    while (terminal.children.length > 50) terminal.removeChild(terminal.firstChild);
}

async function simulateTrap(id, name) {
    addTerminalLine(`Simulation déclenchée sur: ${name}`, 't-yellow');
    const res  = await csrfFetch(`/honeypot/simulate/${id}`, { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        const i = data.interaction;
        addTerminalLine(`INTRUS DÉTECTÉ: ${i.ip} (${i.city}, ${i.country}) — Score: ${i.risk_score}/100`, 't-red');
        if (i.credentials) {
            addTerminalLine(`CREDENTIALS: user=${i.credentials.username} pass=${i.credentials.password}`, 't-red');
        }
        if (i.actions?.length) {
            addTerminalLine(`ACTIONS: ${i.actions.join(' → ')}`, 't-yellow');
        }
        showToast(`🍯 Piège déclenché: ${name}`, `IP: ${i.ip} (${i.country}) — Risk: ${i.risk_score}/100`,
            i.risk_score >= 85 ? 'critical' : 'high');
        if (i.risk_score >= 85) triggerAlarm('high');
        loadLiveStats();
    }
}

async function simulateAll() {
    const traps = document.querySelectorAll('.trap-card.active');
    addTerminalLine(`Simulation globale sur ${traps.length} pièges actifs`, 't-cyan');
    if (traps.length === 0) { showToast('⚠️', 'Aucun piège actif. Initialisez d\'abord.', 'medium'); return; }
    const id = traps[Math.floor(Math.random() * traps.length)].id.replace('trap-', '');
    const nameEl = document.querySelector(`#trap-${id} .trap-name`);
    if (id && nameEl) simulateTrap(parseInt(id), nameEl.textContent);
}

async function initializeTraps() {
    addTerminalLine('Initialisation des pièges...', 't-cyan');
    const res  = await csrfFetch('/honeypot/initialize', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        addTerminalLine('✓ Tous les pièges déployés avec succès', 't-green');
        showToast('🍯 Initialisé', 'Pièges honeypot déployés', 'low');
        setTimeout(() => location.reload(), 1500);
    }
}

async function toggleTrap(id, btn) {
    const res  = await csrfFetch(`/honeypot/toggle/${id}`, { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        const card = document.getElementById(`trap-${id}`);
        card.className = card.className.replace(/active|inactive|triggered/, data.status);
        addTerminalLine(`Piège #${id} ${data.status === 'active' ? 'activé' : 'désactivé'}`, data.status === 'active' ? 't-green' : 't-yellow');
    }
}

async function loadLiveStats() {
    try {
        const res  = await fetch('/honeypot/live-stats');
        const data = await res.json();
        document.getElementById('hp-total').textContent  = data.total;
        document.getElementById('hp-unique').textContent = data.unique_ips;
        document.getElementById('hp-creds').textContent  = data.creds;

        if (data.interactions.length > 0) {
            const latest = data.interactions[0];
            if (latest.id > lastInteractionId) {
                lastInteractionId = latest.id;
                prependInteraction(latest);
                addTerminalLine(`NOUVEL INTRUS: ${latest.ip} → ${latest.trap_name}`, 't-red');
            }
        }
    } catch (e) {}
}

function prependInteraction(i) {
    const feed   = document.getElementById('interaction-feed');
    const isHigh = i.risk_score >= 85;
    const item   = document.createElement('div');
    item.className = `interaction-item ${isHigh ? 'high-risk' : 'med-risk'}`;
    item.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
            <div style="flex:1;">
                <div style="font-size:12px;font-weight:700;">${i.trap_name}</div>
                <div class="mono" style="font-size:11px;color:var(--accent-cyan);">${i.ip}</div>
                <div style="font-size:11px;color:var(--text-muted);">🌍 ${i.city}, ${i.country}</div>
                ${i.credentials ? `<div class="creds-box"><span>👤</span><span class="creds-user">${i.credentials.username}</span><span style="color:var(--text-muted)">:</span><span class="creds-pass">${i.credentials.password}</span></div>` : ''}
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-family:'Rajdhani',sans-serif;font-size:22px;font-weight:700;color:${isHigh ? 'var(--accent-red)' : 'var(--accent-yellow)'};">${i.risk_score}</div>
                <div style="font-size:10px;color:var(--text-muted);">RISQUE</div>
                <div style="font-size:10px;color:var(--text-muted);">À l'instant</div>
            </div>
        </div>
        <div class="risk-bar"><div class="risk-fill" style="width:${i.risk_score}%;background:${isHigh ? 'var(--accent-red)' : 'var(--accent-yellow)'};"></div></div>
    `;
    if (feed.firstElementChild?.style?.textAlign === 'center') feed.innerHTML = '';
    feed.insertBefore(item, feed.firstChild);
    if (feed.children.length > 15) feed.removeChild(feed.lastChild);
}

// Polling live stats
setInterval(loadLiveStats, 7000);

// Terminal heartbeat
setInterval(() => {
    if (Math.random() < 0.3) {
        const msgs = [
            'Surveillance réseau active...',
            'Analyse des paquets entrants...',
            'Aucune activité suspecte détectée',
            'Vérification intégrité des pièges...',
        ];
        addTerminalLine(msgs[Math.floor(Math.random() * msgs.length)], 't-gray');
    }
}, 5000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hp\Desktop\cyberguard\cyberguard\resources\views/honeypot/index.blade.php ENDPATH**/ ?>