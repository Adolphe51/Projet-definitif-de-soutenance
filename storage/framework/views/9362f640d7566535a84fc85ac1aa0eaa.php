<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'CyberGuard'); ?> — Système de Défense</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@400;600;700&family=Exo+2:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/cyberguard.css">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
<div id="alarm-overlay"></div>
<div id="alarm-banner">⚠ ALERTE SYSTÈME ⚠ ALERTE SYSTÈME ⚠ ATTAQUE DÉTECTÉE ⚠ ALERTE SYSTÈME ⚠</div>
<div id="toast-container"></div>
<aside class="sidebar">
    <div class="logo">
        <div class="logo-icon">🛡️</div>
        <div class="logo-text">
            <div class="brand">CYBERGUARD</div>
            <div class="tagline">SYSTÈME DE DÉFENSE v2.0</div>
        </div>
    </div>
    <div class="system-status">
        <div class="status-dot"></div>
        <span class="status-text">SYSTÈME OPÉRATIONNEL</span>
    </div>
    <nav>
        <div class="nav-section">SURVEILLANCE</div>
        <a href="<?php echo e(route('dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span><span>Tableau de Bord</span>
        </a>
        <a href="<?php echo e(route('attacks.live')); ?>" class="nav-item <?php echo e(request()->routeIs('attacks.live') ? 'active' : ''); ?>">
            <span class="nav-icon"><i class="fas fa-broadcast-tower"></i></span><span>Détection Live</span>
            <span class="nav-badge" style="background:var(--accent-green);color:#000;font-size:9px;">LIVE</span>
        </a>
        <a href="<?php echo e(route('attacks.index')); ?>" class="nav-item <?php echo e(request()->routeIs('attacks.index') ? 'active' : ''); ?>">
            <span class="nav-icon"><i class="fas fa-skull-crossbones"></i></span><span>Attaques</span>
        </a>
        <div class="nav-section">ANALYSE</div>
        <a href="<?php echo e(route('geo.attackers')); ?>" class="nav-item <?php echo e(request()->routeIs('geo.*') ? 'active' : ''); ?>">
            <span class="nav-icon"><i class="fas fa-map-marked-alt"></i></span><span>Carte Géo</span>
        </a>
        <a href="<?php echo e(route('alerts.index')); ?>" class="nav-item <?php echo e(request()->routeIs('alerts.*') ? 'active' : ''); ?>">
            <span class="nav-icon"><i class="fas fa-bell"></i></span><span>Alertes</span>
            <span class="nav-badge" id="nav-alert-count">0</span>
        </a>
        <div class="nav-section">OUTILS</div>
        <a href="<?php echo e(route('simulations.index')); ?>" class="nav-item <?php echo e(request()->routeIs('simulations.*') ? 'active' : ''); ?>">
            <span class="nav-icon"><i class="fas fa-flask"></i></span><span>Simulations</span>
        </a>
        <a href="<?php echo e(route('honeypot.index')); ?>" class="nav-item <?php echo e(request()->routeIs('honeypot.*') ? 'active' : ''); ?>">
            <span class="nav-icon">🍯</span><span>Honeypot</span>
            <?php try{$hp=\App\Models\HoneypotTrap::where('status','triggered')->count();}catch(\Exception $e){$hp=0;} ?>
            <?php if($hp>0): ?><span class="nav-badge" style="background:var(--accent-yellow);color:#000;"><?php echo e($hp); ?></span><?php endif; ?>
        </a>
    </nav>
    <div style="padding:16px 20px;border-top:1px solid var(--border);">
        <div style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--text-muted);">
            <div id="clock" style="color:var(--accent-cyan);font-size:14px;margin-bottom:2px;"></div>
            <div id="date-display"></div>
        </div>
    </div>
</aside>
<main class="main-content">
    <div class="topbar">
        <h1 class="topbar-title"><?php echo $__env->yieldContent('page-title','Tableau de Bord'); ?></h1>
        <div class="threat-level normal" id="threat-indicator"><span>●</span><span id="threat-text">ÉVALUATION...</span></div>
        <button class="topbar-btn" onclick="triggerManualAlarm()" title="Test alarme"><i class="fas fa-volume-up"></i></button>
        <a href="<?php echo e(route('alerts.index')); ?>" class="topbar-btn" style="color:inherit;text-decoration:none;">
            <i class="fas fa-bell"></i><span class="alert-badge" id="topbar-alert-count">0</span>
        </a>
        <button class="topbar-btn" onclick="location.reload()" title="Actualiser"><i class="fas fa-sync-alt"></i></button>
    </div>
    <div class="page-content"><?php echo $__env->yieldContent('content'); ?></div>
</main>
<script src="/js/cyberguard.js"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\hp\Desktop\cyberguard\cyberguard\resources\views/layouts/app.blade.php ENDPATH**/ ?>