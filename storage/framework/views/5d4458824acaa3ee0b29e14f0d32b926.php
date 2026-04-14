<?php $__env->startSection('title', 'Tableau de Bord — CyberGuard'); ?>
<?php $__env->startSection('page-title', '🛡️ Tableau de Bord'); ?>

<?php $__env->startSection('content'); ?>

<!-- Stats principales -->
<div class="stats-grid">
    <div class="stat-card stat-attacks">
        <div class="stat-value" id="stat-total"><?php echo e($stats['total_attacks']); ?></div>
        <div class="stat-label">Total Attaques</div>
        <div class="stat-icon">💀</div>
    </div>

    <div class="stat-card stat-critical">
        <div class="stat-value" id="stat-critical"><?php echo e($stats['critical']); ?></div>
        <div class="stat-label">Critiques</div>
        <div class="stat-icon">🔴</div>
    </div>

    <div class="stat-card stat-blocked">
        <div class="stat-value" id="stat-blocked"><?php echo e($stats['blocked']); ?></div>
        <div class="stat-label">IPs Bloquées</div>
        <div class="stat-icon">🛡️</div>
    </div>

    <div class="stat-card stat-active">
        <div class="stat-value" id="stat-active"><?php echo e($stats['active']); ?></div>
        <div class="stat-label">Actives Now</div>
        <div class="stat-icon">⚡</div>
    </div>
</div>

<!-- Stats secondaires -->
<div class="stats-grid-secondary">
    <div class="stat-card stat-countries">
        <div class="stat-value" id="stat-countries"><?php echo e($stats['countries_count']); ?></div>
        <div class="stat-label">Pays Sources</div>
        <div class="stat-icon">🌍</div>
    </div>

    <div class="stat-card stat-perhour">
        <div class="stat-value" id="stat-perhour"><?php echo e($stats['attacks_per_hour']); ?></div>
        <div class="stat-label">Attaques / Heure</div>
        <div class="stat-icon">⏱️</div>
    </div>

    <div class="stat-card stat-blocked-ips">
        <div class="stat-value" id="stat-blocked-ips"><?php echo e($stats['blocked_ips_count']); ?></div>
        <div class="stat-label">IPs Bloquées</div>
        <div class="stat-icon">🛑</div>
    </div>

    <div class="stat-card stat-honeypots">
        <div class="stat-value" id="stat-active-honeypots"><?php echo e($stats['active_honeypots']); ?></div>
        <div class="stat-label">Honeypots Actifs</div>
        <div class="stat-icon">🎣</div>
    </div>
</div>

<!-- Live feed et graphique attaques -->
<div class="grid-2">
    <!-- Flux en temps réel -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Flux en Temps Réel</div>
            <a href="#" class="btn btn-sm btn-primary">Live</a>
        </div>
        <div id="live-feed">
            <?php $__currentLoopData = $recentAttacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attack): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="feed-item">
                <span class="feed-icon"><?php echo e($attack->type_icon); ?></span>
                <div class="feed-content">
                    <div class="feed-title">
                        <span><?php echo e($attack->type); ?></span>
                        <span class="badge badge-<?php echo e($attack->severity); ?>"><?php echo e($attack->severity); ?></span>
                        <?php if($attack->is_simulation): ?>
                        <span class="badge badge-simulation">SIM</span>
                        <?php endif; ?>
                    </div>
                    <div class="feed-details">
                        <span class="ip"><?php echo e($attack->source_ip); ?></span> → <?php echo e($attack->city); ?>, <?php echo e($attack->country); ?>

                    </div>
                </div>
                <div class="feed-time"><?php echo e($attack->created_at->diffForHumans()); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Graphique types d'attaques -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Types d'Attaques</div>
        </div>
        <canvas id="attackChart" height="280"></canvas>
    </div>
</div>

<!-- Alertes et interactions Honeypot -->
<div class="grid-2">
    <!-- Alertes récentes -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Alertes Récentes</div>
            <a href="#" class="btn btn-sm btn-primary">Toutes</a>
        </div>
        <div id="alerts-list">
            <?php $__empty_1 = true; $__currentLoopData = $recentAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="alert-item alert-<?php echo e($alert->severity); ?>">
                <div class="alert-icon">
                    <?php echo e($alert->severity === 'critical' ? '💀' : ($alert->severity === 'high' ? '🔴' : '⚠️')); ?>

                </div>
                <div class="alert-content">
                    <div class="alert-title"><?php echo e($alert->title); ?></div>
                    <div class="alert-message"><?php echo e($alert->message); ?></div>
                    <div class="alert-time"><?php echo e($alert->created_at->diffForHumans()); ?></div>
                </div>
                <?php if(!$alert->acknowledged): ?>
                <span class="alert-unread"></span>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="no-alerts">Aucune alerte récente</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Interactions Honeypot récentes -->
    <div class="card">
        <div class="section-header">
            <div class="section-title">Interactions Honeypot Récentes</div>
        </div>
        <div id="honeypot-interactions">
            <?php $__empty_1 = true; $__currentLoopData = $recentInteractions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="honeypot-item">
                <div class="honeypot-ip"><?php echo e($interaction->source_ip); ?></div>
                <div class="honeypot-trap"><?php echo e($interaction->trap->name ?? 'N/A'); ?></div>
                <div class="honeypot-location"><?php echo e($interaction->city); ?>, <?php echo e($interaction->country); ?></div>
                <div class="honeypot-time"><?php echo e($interaction->created_at->diffForHumans()); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="no-interactions">Aucune interaction récente</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/Projet_Soutenance/Projet-definitif-de-soutenance/resources/views/dashboard/index.blade.php ENDPATH**/ ?>