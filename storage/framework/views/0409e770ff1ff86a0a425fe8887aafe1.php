<?php $__env->startSection('title', 'Tableau de Bord — CyberGuard'); ?>
<?php $__env->startSection('page-title', '🛡️ Tableau de Bord'); ?>

<?php $__env->startSection('content'); ?>
<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card" style="--accent-color: var(--accent-red);">
        <div class="stat-value" id="stat-total"><?php echo e($stats['total_attacks']); ?></div>
        <div class="stat-label">Total Attaques</div>
        <div class="stat-icon">💀</div>
    </div>
    <div class="stat-card" style="--accent-color: var(--accent-red);">
        <div class="stat-value" id="stat-critical"><?php echo e($stats['critical']); ?></div>
        <div class="stat-label">Critiques</div>
        <div class="stat-icon">🔴</div>
    </div>
    <div class="stat-card" style="--accent-color: var(--accent-green);">
        <div class="stat-value" id="stat-blocked"><?php echo e($stats['blocked']); ?></div>
        <div class="stat-label">IPs Bloquées</div>
        <div class="stat-icon">🛡️</div>
    </div>
    <div class="stat-card" style="--accent-color: var(--accent-orange);">
        <div class="stat-value" id="stat-active"><?php echo e($stats['active']); ?></div>
        <div class="stat-label">Actives Now</div>
        <div class="stat-icon">⚡</div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 24px;">
    <div class="stat-card" style="--accent-color: var(--accent-cyan);">
        <div class="stat-value" id="stat-countries"><?php echo e($stats['countries_count']); ?></div>
        <div class="stat-label">Pays Sources</div>
        <div class="stat-icon">🌍</div>
    </div>
    <div class="stat-card" style="--accent-color: var(--accent-yellow);">
        <div class="stat-value" id="stat-perhour"><?php echo e($stats['attacks_per_hour']); ?></div>
        <div class="stat-label">Attaques / Heure</div>
        <div class="stat-icon">⏱️</div>
    </div>
</div>

<!-- Live attack feed & Activity chart -->
<div class="grid-2" style="margin-bottom: 24px;">

    <!-- Live Feed -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Flux en Temps Réel</div>
            <a href="<?php echo e(route('attacks.live')); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-broadcast-tower"></i> Live
            </a>
        </div>
        <div id="live-feed" style="max-height: 340px; overflow-y: auto;">
            <?php $__currentLoopData = $recentAttacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attack): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="feed-item" style="
                display: flex; align-items: center; gap: 10px;
                padding: 10px 0; border-bottom: 1px solid rgba(26,58,92,0.4);
            ">
                <span style="font-size: 18px;"><?php echo e($attack->type_icon); ?></span>
                <div style="flex: 1; min-width: 0;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-weight:600; font-size:13px;"><?php echo e($attack->type); ?></span>
                        <span class="badge badge-<?php echo e($attack->severity); ?>"><?php echo e($attack->severity); ?></span>
                        <?php if($attack->is_simulation): ?>
                        <span class="badge" style="background:rgba(168,85,247,0.15);color:#a855f7;border-color:#a855f7;">SIM</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--text-muted); margin-top:2px;">
                        <span class="ip-addr"><?php echo e($attack->source_ip); ?></span>
                        <span style="margin: 0 4px;">→</span>
                        <?php echo e($attack->city); ?>, <?php echo e($attack->country); ?>

                    </div>
                </div>
                <div style="font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--text-muted); text-align:right;">
                    <?php echo e($attack->created_at->diffForHumans()); ?>

                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Attack type chart -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Types d'Attaques</div>
        </div>
        <canvas id="attackChart" height="280"></canvas>
    </div>
</div>

<!-- Alerts + Map preview -->
<div class="grid-2">
    <!-- Recent alerts -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Alertes Récentes</div>
            <a href="<?php echo e(route('alerts.index')); ?>" class="btn btn-primary btn-sm">Toutes</a>
        </div>
        <div id="alerts-list">
            <?php $__empty_1 = true; $__currentLoopData = $recentAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="
                display:flex; gap:10px; padding:10px; margin-bottom:8px;
                background:rgba(13,30,45,0.6); border-radius:8px;
                border-left:3px solid var(--<?php echo e($alert->severity); ?>, #aaa);
            ">
                <div style="font-size:16px;">
                    <?php echo e($alert->severity === 'critical' ? '💀' : ($alert->severity === 'high' ? '🔴' : '⚠️')); ?>

                </div>
                <div style="flex:1;">
                    <div style="font-size:13px; font-weight:600;"><?php echo e($alert->title); ?></div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px;"><?php echo e($alert->message); ?></div>
                    <div style="font-size:10px; color:var(--text-muted); margin-top:4px; font-family:'Share Tech Mono',monospace;">
                        <?php echo e($alert->created_at->diffForHumans()); ?>

                    </div>
                </div>
                <?php if(!$alert->acknowledged): ?>
                <span style="width:6px;height:6px;background:var(--accent-red);border-radius:50%;margin-top:4px;box-shadow:0 0 6px var(--accent-red);flex-shrink:0;"></span>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align:center; padding:40px; color:var(--text-muted);">
                <div style="font-size:32px; margin-bottom:8px;">✅</div>
                Aucune alerte récente
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick actions + mini stats -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Actions Rapides</div>
        </div>

        <div style="display:grid; gap:10px; margin-bottom:20px;">
            <button class="btn btn-danger" onclick="triggerManualAlarm()" style="justify-content:center; padding:14px;">
                <i class="fas fa-exclamation-triangle"></i> Déclencher Alarme Test
            </button>
            <a href="<?php echo e(route('attacks.live')); ?>" class="btn btn-primary" style="justify-content:center; padding:14px; text-align:center;">
                <i class="fas fa-broadcast-tower"></i> Moniteur Live
            </a>
            <a href="<?php echo e(route('simulations.index')); ?>" class="btn btn-warning" style="justify-content:center; padding:14px; text-align:center;">
                <i class="fas fa-flask"></i> Lancer une Simulation
            </a>
            <a href="<?php echo e(route('geo.attackers')); ?>" class="btn btn-success" style="justify-content:center; padding:14px; text-align:center;">
                <i class="fas fa-map-marked-alt"></i> Carte Géographique
            </a>
        </div>

        <div style="background:rgba(0,229,255,0.05); border:1px solid rgba(0,229,255,0.15); border-radius:8px; padding:16px;">
            <div style="font-family:'Share Tech Mono',monospace; font-size:11px; color:var(--text-muted); margin-bottom:10px;">STATISTIQUES SYSTÈME</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:12px;">
                <div><span style="color:var(--text-muted);">Type dominant:</span><br><strong style="color:var(--accent-cyan);"><?php echo e($stats['top_attack_type']); ?></strong></div>
                <div><span style="color:var(--text-muted);">IPs à risque:</span><br><strong style="color:var(--accent-red);"><?php echo e($stats['high_risk_ips']); ?></strong></div>
                <div><span style="color:var(--text-muted);">Simulations:</span><br><strong style="color:var(--accent-purple);"><?php echo e($stats['simulations_run']); ?></strong></div>
                <div><span style="color:var(--text-muted);">Alertes actives:</span><br><strong style="color:var(--accent-yellow);"><?php echo e($stats['unread_alerts']); ?></strong></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
// Chart des types d'attaques
const ctx = document.getElementById('attackChart').getContext('2d');
const attackData = <?php echo json_encode(\App\Models\Attack::selectRaw('type, COUNT(*) as cnt')->groupBy('type')->orderByDesc('cnt')->limit(8)->pluck('cnt', 'type')); ?>;

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(attackData),
        datasets: [{
            data: Object.values(attackData),
            backgroundColor: [
                '#ff0040','#ff6b00','#ffd600','#00ff88',
                '#00e5ff','#a855f7','#ec4899','#3b82f6'
            ],
            borderColor: '#0a1520',
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: { color: '#4a7a9b', font: { family: 'Share Tech Mono', size: 11 }, boxWidth: 12 }
            }
        }
    }
});

// Auto-refresh stats
let prevTotal = <?php echo e($stats['total_attacks']); ?>;
setInterval(async () => {
    try {
        const res  = await fetch('/api/stats');
        const data = await res.json();
        document.getElementById('stat-total').textContent    = data.total_attacks;
        document.getElementById('stat-critical').textContent = data.critical;
        document.getElementById('stat-blocked').textContent  = data.blocked;
        document.getElementById('stat-active').textContent   = data.active;
        document.getElementById('stat-countries').textContent = data.countries_count;
        document.getElementById('stat-perhour').textContent  = data.attacks_per_hour;

        // Si nouvelle attaque
        if (data.total_attacks > prevTotal) {
            prevTotal = data.total_attacks;
            if (data.critical > 0) {
                triggerAlarm('critical');
                showToast('💀 ATTAQUE CRITIQUE!', 'Nouvelle attaque critique détectée!', 'critical');
            }
        }
    } catch (e) {}
}, 8000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hp\Desktop\cyberguard\cyberguard\resources\views/dashboard/index.blade.php ENDPATH**/ ?>