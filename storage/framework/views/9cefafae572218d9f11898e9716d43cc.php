<?php $__env->startSection('title', 'Simulations — CyberGuard'); ?>
<?php $__env->startSection('page-title', '⚗️ Centre de Simulation'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.sim-grid { display: grid; grid-template-columns: 380px 1fr; gap: 24px; }

.launch-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
}

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-family: 'Share Tech Mono', monospace; font-size: 11px; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 1px; }

.form-control {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--text-primary);
    padding: 10px 12px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
}

.form-control:focus { border-color: var(--accent-cyan); box-shadow: 0 0 0 3px rgba(0,229,255,0.1); }

select.form-control { cursor: pointer; }

.intensity-selector { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }

.intensity-opt {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--bg-secondary);
    color: var(--text-muted);
    cursor: pointer;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.intensity-opt.selected { border-color: var(--opt-color, var(--accent-cyan)); color: var(--opt-color, var(--accent-cyan)); background: rgba(0,229,255,0.08); }

.sim-progress {
    display: none;
    background: var(--bg-card2);
    border: 1px solid rgba(0,229,255,0.2);
    border-radius: 10px;
    padding: 20px;
    margin-top: 16px;
}

.progress-bar-wrap {
    background: var(--bg-secondary);
    border-radius: 4px;
    height: 8px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green));
    border-radius: 4px;
    transition: width 0.5s;
    width: 0%;
    box-shadow: 0 0 10px rgba(0,229,255,0.5);
}

.sim-log {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    height: 200px;
    overflow-y: auto;
    color: var(--accent-green);
    line-height: 1.7;
}

.log-line { padding: 1px 0; }
.log-line.warn  { color: var(--accent-yellow); }
.log-line.error { color: var(--accent-red); }
.log-line.info  { color: var(--accent-cyan); }

.history-table-wrap { overflow-x: auto; }

.sim-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 8px; border-radius: 4px;
    font-family: 'Share Tech Mono', monospace; font-size: 11px;
}
.sim-status.running   { background: rgba(0,255,136,0.1);  color: var(--accent-green); border: 1px solid rgba(0,255,136,0.3); }
.sim-status.completed { background: rgba(0,229,255,0.1);  color: var(--accent-cyan);  border: 1px solid rgba(0,229,255,0.3); }
.sim-status.stopped   { background: rgba(255,107,0,0.1);  color: var(--accent-orange);border: 1px solid rgba(255,107,0,0.3); }
.sim-status.pending   { background: rgba(255,214,0,0.1);  color: var(--accent-yellow);border: 1px solid rgba(255,214,0,0.3); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="sim-grid">

    <!-- Panneau de lancement -->
    <div>
        <div class="launch-panel">
            <div class="section-title" style="margin-bottom: 20px;">🚀 Lancer Simulation</div>

            <div class="form-group">
                <label class="form-label">Type d'Attaque</label>
                <select class="form-control" id="sim-type">
                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type); ?>"><?php echo e($type); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">IP Cible</label>
                <input type="text" class="form-control" id="sim-target" value="192.168.1.100" placeholder="ex: 10.0.0.1">
            </div>

            <div class="form-group">
                <label class="form-label">Durée (secondes): <span id="dur-display" style="color:var(--accent-cyan);">30s</span></label>
                <input type="range" class="form-control" id="sim-duration" min="5" max="120" value="30"
                    oninput="document.getElementById('dur-display').textContent = this.value + 's'"
                    style="padding: 4px 0; background: none; border: none;">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted);">
                    <span>5s</span><span>60s</span><span>120s</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Intensité</label>
                <div class="intensity-selector">
                    <div class="intensity-opt" style="--opt-color:var(--accent-green);" onclick="selectIntensity('low', this)">
                        <div style="font-size:16px; margin-bottom:4px;">🟢</div>
                        Faible
                    </div>
                    <div class="intensity-opt selected" style="--opt-color:var(--accent-yellow);" onclick="selectIntensity('medium', this)">
                        <div style="font-size:16px; margin-bottom:4px;">🟡</div>
                        Moyen
                    </div>
                    <div class="intensity-opt" style="--opt-color:var(--accent-red);" onclick="selectIntensity('high', this)">
                        <div style="font-size:16px; margin-bottom:4px;">🔴</div>
                        Élevé
                    </div>
                </div>
                <input type="hidden" id="sim-intensity" value="medium">
            </div>

            <button class="btn btn-warning" style="width:100%; justify-content:center; padding:14px;" id="launch-btn" onclick="launchSimulation()">
                <i class="fas fa-play"></i> Lancer la Simulation
            </button>

            <button class="btn btn-danger" style="width:100%; justify-content:center; margin-top:8px; display:none;" id="stop-btn" onclick="stopSimulation()">
                <i class="fas fa-stop"></i> Arrêter
            </button>
        </div>

        <!-- Progress -->
        <div class="sim-progress" id="sim-progress">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <span style="font-family:'Share Tech Mono',monospace; font-size:12px; color:var(--accent-cyan);">SIMULATION EN COURS</span>
                <span id="sim-elapsed" style="font-family:'Rajdhani',sans-serif; font-size:20px; color:var(--accent-green);">0s</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" id="progress-fill"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text-muted); margin-bottom:12px;">
                <span>Paquets envoyés: <strong id="sim-packets" style="color:var(--accent-cyan);">0</strong></span>
                <span id="sim-percent">0%</span>
            </div>

            <div class="sim-log" id="sim-log">
                <div class="log-line info">// Journal de simulation</div>
            </div>
        </div>
    </div>

    <!-- Panel droit: feed + historique -->
    <div>
        <!-- Feed simulation -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="section-header">
                <div class="section-title">Flux de Simulation</div>
                <span id="sim-badge" class="badge badge-info">EN ATTENTE</span>
            </div>
            <div id="sim-feed" style="max-height: 280px; overflow-y: auto;">
                <div style="text-align:center; padding:40px; color:var(--text-muted);">
                    <div style="font-size:36px; margin-bottom:12px;">⚗️</div>
                    Lancez une simulation pour voir le flux
                </div>
            </div>
        </div>

        <!-- Historique -->
        <div class="card">
            <div class="section-header">
                <div class="section-title">Historique des Simulations</div>
            </div>
            <div class="history-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Cible</th>
                            <th>Durée</th>
                            <th>Intensité</th>
                            <th>Paquets</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody id="sim-history">
                        <?php $__currentLoopData = $simulations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="mono" style="font-size:12px;"><?php echo e($sim->name); ?></td>
                            <td><span class="badge badge-info"><?php echo e($sim->attack_type); ?></span></td>
                            <td class="ip-addr"><?php echo e($sim->target_ip); ?></td>
                            <td><?php echo e($sim->duration_seconds); ?>s</td>
                            <td>
                                <?php if($sim->intensity === 'high'): ?>   <span style="color:var(--accent-red);">🔴 Élevé</span>
                                <?php elseif($sim->intensity === 'medium'): ?> <span style="color:var(--accent-yellow);">🟡 Moyen</span>
                                <?php else: ?> <span style="color:var(--accent-green);">🟢 Faible</span>
                                <?php endif; ?>
                            </td>
                            <td class="mono"><?php echo e(number_format($sim->packets_sent)); ?></td>
                            <td><span class="sim-status <?php echo e($sim->status); ?>"><?php echo e(strtoupper($sim->status)); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let currentSimId   = null;
let simInterval    = null;
let simStartTime   = null;
let simDuration    = 30;
let simPackets     = 0;

function selectIntensity(val, el) {
    document.querySelectorAll('.intensity-opt').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('sim-intensity').value = val;
}

async function launchSimulation() {
    const type      = document.getElementById('sim-type').value;
    const target    = document.getElementById('sim-target').value;
    const duration  = parseInt(document.getElementById('sim-duration').value);
    const intensity = document.getElementById('sim-intensity').value;

    if (!target.match(/^(\d{1,3}\.){3}\d{1,3}$/)) {
        showToast('❌ Erreur', 'Adresse IP invalide', 'medium');
        return;
    }

    simDuration = duration;
    simPackets  = 0;

    const res  = await csrfFetch('/simulations/launch', {
        method: 'POST',
        body: JSON.stringify({ attack_type: type, target_ip: target, duration, intensity })
    });
    const data = await res.json();

    if (data.success) {
        currentSimId   = data.simulation_id;
        simStartTime   = Date.now();
        document.getElementById('sim-progress').style.display = 'block';
        document.getElementById('launch-btn').style.display   = 'none';
        document.getElementById('stop-btn').style.display     = 'block';
        document.getElementById('sim-badge').textContent      = 'EN COURS';
        document.getElementById('sim-badge').className        = 'badge badge-low';

        addLog(`[${now()}] Simulation ${type} démarrée vers ${target}`, 'info');
        addLog(`[${now()}] Intensité: ${intensity} | Durée: ${duration}s`, 'info');

        simInterval = setInterval(runSimStep, 1500);

        showToast('🚀 Simulation lancée', `${type} → ${target}`, 'low');

        // Déclencher alarme si intensité haute
        if (intensity === 'high') {
            triggerAlarm('high');
        }
    }
}

async function runSimStep() {
    const elapsed = (Date.now() - simStartTime) / 1000;
    const pct     = Math.min((elapsed / simDuration) * 100, 100);

    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('sim-elapsed').textContent   = Math.floor(elapsed) + 's';
    document.getElementById('sim-percent').textContent   = Math.round(pct) + '%';

    if (elapsed >= simDuration) {
        stopSimulation(true);
        return;
    }

    try {
        const res  = await csrfFetch(`/api/simulate?simulation_id=${currentSimId}`, { method: 'POST', body: JSON.stringify({}) });
        const data = await res.json();

        if (data.status === 'completed') { stopSimulation(true); return; }

        if (data.attack) {
            const a = data.attack;
            simPackets += a.packets || 0;
            document.getElementById('sim-packets').textContent = Number(simPackets).toLocaleString();

            addLog(`[${now()}] PKT→ ${a.source_ip} (${a.city}, ${a.country}) | ${a.severity.toUpperCase()} | ${Number(a.packets).toLocaleString()} pkts`, 
                a.severity === 'critical' ? 'error' : a.severity === 'high' ? 'warn' : '');

            // Ajouter au feed
            prependFeed(a);
        }
    } catch (e) {}
}

function prependFeed(a) {
    const feed    = document.getElementById('sim-feed');
    const item    = document.createElement('div');
    item.style.cssText = 'display:flex;gap:10px;padding:8px 0;border-bottom:1px solid rgba(26,58,92,0.4);animation:rowAppear 0.3s ease-out;';
    item.innerHTML = `
        <span style="color:var(--accent-cyan);font-family:'Share Tech Mono',monospace;font-size:11px;">${now()}</span>
        <span class="badge badge-${a.severity}">${a.severity}</span>
        <span style="font-size:12px;flex:1;">${a.type} • <span class="ip-addr">${a.source_ip}</span> • ${a.country}</span>
        <span style="font-size:11px;color:var(--text-muted);">${Number(a.packets).toLocaleString()} pkts</span>
    `;
    if (feed.firstChild?.style?.textAlign === 'center') feed.innerHTML = '';
    feed.insertBefore(item, feed.firstChild);
    if (feed.children.length > 30) feed.removeChild(feed.lastChild);
}

function stopSimulation(completed = false) {
    clearInterval(simInterval);
    document.getElementById('launch-btn').style.display = 'block';
    document.getElementById('stop-btn').style.display   = 'none';
    document.getElementById('sim-badge').textContent    = completed ? 'TERMINÉ' : 'ARRÊTÉ';
    document.getElementById('sim-badge').className      = 'badge badge-info';

    if (currentSimId && !completed) {
        csrfFetch(`/simulations/stop/${currentSimId}`, { method: 'POST' });
    }

    addLog(`[${now()}] ✅ Simulation ${completed ? 'complétée' : 'arrêtée'} | Paquets totaux: ${Number(simPackets).toLocaleString()}`, 'info');
    stopAlarm();
    showToast('⚗️ Simulation ' + (completed ? 'terminée' : 'arrêtée'), `${Number(simPackets).toLocaleString()} paquets envoyés`, 'low');
    currentSimId = null;
}

function addLog(msg, cls = '') {
    const log  = document.getElementById('sim-log');
    const line = document.createElement('div');
    line.className = `log-line ${cls}`;
    line.textContent = msg;
    log.appendChild(line);
    log.scrollTop = log.scrollHeight;
}

function now() {
    return new Date().toLocaleTimeString('fr-FR', {hour12: false});
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hp\Desktop\cyberguard\cyberguard\resources\views/simulations/index.blade.php ENDPATH**/ ?>