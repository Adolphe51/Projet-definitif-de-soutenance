<?php $__env->startSection('title', 'Carte Géographique — CyberGuard'); ?>
<?php $__env->startSection('page-title', '🌍 Géolocalisation des Attaquants'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.geo-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }

#world-map {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    height: 480px;
    position: relative;
    overflow: hidden;
}

#map-svg {
    width: 100%; height: 100%;
}

.map-overlay {
    position: absolute;
    top: 12px; left: 12px;
    display: flex; gap: 8px;
}

.map-legend {
    position: absolute;
    bottom: 12px; left: 12px;
    background: rgba(7,13,21,0.9);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 10px 14px;
    display: flex; gap: 16px;
    font-size: 11px;
    font-family: 'Share Tech Mono', monospace;
}

.legend-item { display: flex; align-items: center; gap: 5px; }
.legend-dot  { width: 8px; height: 8px; border-radius: 50%; }

.attacker-list {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    max-height: 480px;
}

.attacker-header {
    padding: 16px;
    border-bottom: 1px solid var(--border);
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: var(--text-muted);
    letter-spacing: 1px;
}

.attacker-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px;
    border-bottom: 1px solid rgba(26,58,92,0.3);
    cursor: pointer;
    transition: background 0.2s;
    font-size: 12px;
}

.attacker-item:hover { background: rgba(0,229,255,0.04); }

.country-flag { font-size: 18px; flex-shrink: 0; }

.attack-line {
    stroke-width: 1.5;
    fill: none;
    stroke-linecap: round;
    animation: dashFlow 2s linear infinite;
}

@keyframes dashFlow {
    from { stroke-dashoffset: 100; }
    to   { stroke-dashoffset: 0; }
}

.pulse-circle {
    animation: mapPulse 1.5s ease-out infinite;
}

@keyframes mapPulse {
    0%   { r: 4; opacity: 0.8; }
    100% { r: 16; opacity: 0; }
}

.stats-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 20px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="stats-row">
    <div class="stat-card" style="--accent-color: var(--accent-cyan);">
        <div class="stat-value" id="geo-total">0</div>
        <div class="stat-label">Attaquants Géolocalisés</div>
    </div>
    <div class="stat-card" style="--accent-color: var(--accent-red);">
        <div class="stat-value" id="geo-critical">0</div>
        <div class="stat-label">Critiques Actifs</div>
    </div>
    <div class="stat-card" style="--accent-color: var(--accent-green);">
        <div class="stat-value" id="geo-blocked">0</div>
        <div class="stat-label">IPs Bloquées</div>
    </div>
</div>

<div class="geo-layout">
    <!-- Carte monde -->
    <div>
        <div class="section-header">
            <div class="section-title">Carte des Attaques en Temps Réel</div>
            <button class="btn btn-primary btn-sm" onclick="refreshGeo()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>

        <div id="world-map">
            <svg id="map-svg" viewBox="0 0 800 450" xmlns="http://www.w3.org/2000/svg">
                <!-- Fond océan -->
                <rect width="800" height="450" fill="#050a0f"/>

                <!-- Grille -->
                <g stroke="rgba(0,229,255,0.05)" stroke-width="0.5">
                    <line x1="0" y1="225" x2="800" y2="225"/>
                    <line x1="400" y1="0" x2="400" y2="450"/>
                    <?php for($i = 0; $i < 8; $i++): ?>
                    <line x1="<?php echo e($i * 100); ?>" y1="0" x2="<?php echo e($i * 100); ?>" y2="450"/>
                    <?php endfor; ?>
                    <?php for($i = 0; $i < 5; $i++): ?>
                    <line x1="0" y1="<?php echo e($i * 90); ?>" x2="800" y2="<?php echo e($i * 90); ?>"/>
                    <?php endfor; ?>
                </g>

                <!-- Continents simplifiés (formes SVG) -->
                <!-- Amérique du Nord -->
                <path d="M80,80 L180,70 L200,90 L210,130 L190,160 L160,180 L120,200 L80,220 L60,200 L50,160 L60,120 Z"
                    fill="rgba(0,229,255,0.08)" stroke="rgba(0,229,255,0.2)" stroke-width="1"/>
                <!-- Amérique du Sud -->
                <path d="M140,220 L185,215 L200,240 L190,310 L170,350 L150,360 L135,330 L120,280 L125,240 Z"
                    fill="rgba(0,229,255,0.08)" stroke="rgba(0,229,255,0.2)" stroke-width="1"/>
                <!-- Europe -->
                <path d="M340,70 L400,65 L420,80 L415,110 L390,125 L360,120 L340,105 L330,85 Z"
                    fill="rgba(0,229,255,0.08)" stroke="rgba(0,229,255,0.2)" stroke-width="1"/>
                <!-- Afrique -->
                <path d="M345,130 L400,125 L430,145 L440,200 L430,270 L400,310 L370,305 L345,270 L335,200 L335,150 Z"
                    fill="rgba(0,229,255,0.08)" stroke="rgba(0,229,255,0.2)" stroke-width="1"/>
                <!-- Asie -->
                <path d="M430,55 L600,50 L650,80 L660,130 L620,155 L560,160 L500,145 L450,130 L425,105 L420,75 Z"
                    fill="rgba(0,229,255,0.08)" stroke="rgba(0,229,255,0.2)" stroke-width="1"/>
                <!-- Russie -->
                <path d="M420,50 L600,45 L650,65 L640,85 L580,90 L500,85 L440,75 Z"
                    fill="rgba(0,229,255,0.06)" stroke="rgba(0,229,255,0.15)" stroke-width="1"/>
                <!-- Australie -->
                <path d="M580,270 L650,265 L680,300 L670,340 L640,355 L600,345 L570,315 L565,285 Z"
                    fill="rgba(0,229,255,0.08)" stroke="rgba(0,229,255,0.2)" stroke-width="1"/>

                <!-- Lignes d'attaque et points — seront rendus dynamiquement -->
                <g id="attack-lines"></g>
                <g id="attack-points"></g>

                <!-- Cible (notre serveur - Cotonou, Bénin) -->
                <circle id="target-point" cx="390" cy="230" r="6" fill="var(--accent-green)" opacity="0.9"/>
                <circle cx="390" cy="230" r="6" fill="none" stroke="var(--accent-green)" stroke-width="1.5" class="pulse-circle"/>
                <circle cx="390" cy="230" r="6" fill="none" stroke="var(--accent-green)" stroke-width="1" opacity="0.4" class="pulse-circle" style="animation-delay:0.5s;"/>
                <text x="398" y="226" font-family="Share Tech Mono" font-size="10" fill="var(--accent-green)">🎯 CIBLE</text>
            </svg>

            <div class="map-legend">
                <div class="legend-item"><div class="legend-dot" style="background:var(--critical);"></div> Critique</div>
                <div class="legend-item"><div class="legend-dot" style="background:var(--high);"></div> Élevé</div>
                <div class="legend-item"><div class="legend-dot" style="background:var(--medium);"></div> Moyen</div>
                <div class="legend-item"><div class="legend-dot" style="background:var(--low);"></div> Faible</div>
                <div class="legend-item"><div class="legend-dot" style="background:var(--accent-green);box-shadow:0 0 6px var(--accent-green);"></div> Cible Protégée</div>
            </div>
        </div>
    </div>

    <!-- Liste attaquants -->
    <div class="attacker-list">
        <div class="attacker-header">
            TOP ATTAQUANTS GÉOLOCALISÉS
            <span id="attacker-count" style="float:right; color:var(--accent-cyan);">0</span>
        </div>
        <div id="attacker-list" style="overflow-y:auto; max-height:420px;">
            <div style="text-align:center; padding:40px; color:var(--text-muted);">
                <i class="fas fa-spinner fa-spin"></i> Chargement...
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Conversion lat/lon → coordonnées SVG (viewBox 800x450)
function latLonToSvg(lat, lon) {
    const x = (lon + 180) * (800 / 360);
    const y = (90 - lat) * (450 / 180);
    return { x, y };
}

const TARGET = { x: 390, y: 230 }; // Cotonou

function getCountryFlag(country) {
    const flags = {
        'Chine': '🇨🇳', 'Russie': '🇷🇺', 'Corée du Nord': '🇰🇵', 'Iran': '🇮🇷',
        'États-Unis': '🇺🇸', 'Allemagne': '🇩🇪', 'France': '🇫🇷', 'Pays-Bas': '🇳🇱',
        'Inde': '🇮🇳', 'Brésil': '🇧🇷', 'Mexique': '🇲🇽', 'Turquie': '🇹🇷',
        'Roumanie': '🇷🇴', 'Ukraine': '🇺🇦', 'Nigeria': '🇳🇬', 'Afrique du Sud': '🇿🇦',
        'Arabie Saoudite': '🇸🇦',
    };
    return flags[country] || '🌍';
}

async function loadGeoData() {
    try {
        const res  = await fetch('/api/geo-data');
        const data = await res.json();

        document.getElementById('geo-total').textContent   = data.stats.total;
        document.getElementById('geo-critical').textContent = data.stats.critical;
        document.getElementById('geo-blocked').textContent  = data.stats.blocked;

        renderMapPoints(data.attacks);
        renderAttackerList(data.attacks);
    } catch (e) { console.error(e); }
}

function renderMapPoints(attacks) {
    const linesG  = document.getElementById('attack-lines');
    const pointsG = document.getElementById('attack-points');
    linesG.innerHTML  = '';
    pointsG.innerHTML = '';

    const severityColors = { critical: '#ff0040', high: '#ff6b00', medium: '#ffd600', low: '#00ff88' };

    attacks.slice(0, 60).forEach((a, i) => {
        if (!a.lat || !a.lon) return;
        const src   = latLonToSvg(a.lat, a.lon);
        const color = severityColors[a.severity] || '#00e5ff';
        const delay = (i * 0.1) % 3;

        // Ligne d'attaque courbe
        const mx = (src.x + TARGET.x) / 2 + (Math.random() - 0.5) * 80;
        const my = (src.y + TARGET.y) / 2 - Math.abs(src.x - TARGET.x) * 0.2;

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        line.setAttribute('d', `M${src.x},${src.y} Q${mx},${my} ${TARGET.x},${TARGET.y}`);
        line.setAttribute('class', 'attack-line');
        line.setAttribute('stroke', color);
        line.setAttribute('stroke-width', a.severity === 'critical' ? '2' : '1');
        line.setAttribute('opacity', a.status === 'blocked' ? '0.15' : '0.4');
        line.setAttribute('stroke-dasharray', '8 4');
        line.style.animationDelay = delay + 's';
        linesG.appendChild(line);

        // Point source
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', src.x);
        circle.setAttribute('cy', src.y);
        circle.setAttribute('r', a.severity === 'critical' ? '5' : '4');
        circle.setAttribute('fill', color);
        circle.setAttribute('opacity', '0.85');

        if (a.severity === 'critical') {
            circle.style.filter = `drop-shadow(0 0 4px ${color})`;
        }

        const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        title.textContent = `${a.type} — ${a.ip}\n${a.city}, ${a.country}\n${a.severity}`;
        circle.appendChild(title);
        pointsG.appendChild(circle);

        // Pulse pour critiques
        if (a.severity === 'critical') {
            const pulse = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            pulse.setAttribute('cx', src.x);
            pulse.setAttribute('cy', src.y);
            pulse.setAttribute('r', '5');
            pulse.setAttribute('fill', 'none');
            pulse.setAttribute('stroke', color);
            pulse.setAttribute('stroke-width', '1');
            pulse.setAttribute('class', 'pulse-circle');
            pulse.style.animationDelay = (delay + 0.3) + 's';
            pointsG.appendChild(pulse);
        }
    });
}

function renderAttackerList(attacks) {
    const list = document.getElementById('attacker-list');
    document.getElementById('attacker-count').textContent = attacks.length;

    if (attacks.length === 0) {
        list.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);">Aucune donnée</div>';
        return;
    }

    // Grouper par pays
    const byCountry = {};
    attacks.forEach(a => {
        if (!byCountry[a.country]) byCountry[a.country] = { count: 0, severity: 'low', city: a.city };
        byCountry[a.country].count++;
        const sev = ['critical','high','medium','low'].indexOf(a.severity);
        const cur = ['critical','high','medium','low'].indexOf(byCountry[a.country].severity);
        if (sev < cur) byCountry[a.country].severity = a.severity;
    });

    const sorted = Object.entries(byCountry).sort((a,b) => b[1].count - a[1].count).slice(0, 20);

    list.innerHTML = sorted.map(([country, info]) => `
        <div class="attacker-item">
            <span class="country-flag">${getCountryFlag(country)}</span>
            <div style="flex:1;">
                <div style="font-weight:600; font-size:13px;">${country}</div>
                <div style="color:var(--text-muted); font-size:11px;">${info.city}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-family:'Rajdhani',sans-serif; font-size:18px; color:var(--accent-cyan);">${info.count}</div>
                <span class="badge badge-${info.severity}">${info.severity}</span>
            </div>
        </div>
    `).join('');
}

function refreshGeo() { loadGeoData(); }

loadGeoData();
setInterval(loadGeoData, 10000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\hp\Desktop\cyberguard\cyberguard\resources\views/attacks/map.blade.php ENDPATH**/ ?>