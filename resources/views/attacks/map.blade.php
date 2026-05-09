@extends('layouts.app')
@section('title', 'Géolocalisation')
@section('page-title', 'Géolocalisation des attaquants')
@section('page-subtitle', 'Visualisation géographique des attaques, regroupement par pays et lecture rapide des zones les plus actives.')

@section('content')
    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Analyse géographique</span>
            <h2>Une carte plus claire pour visualiser d’où viennent les attaques et où concentrer l’analyse.</h2>
            <p>
                Cette vue regroupe la dimension spatiale du module CyberGuard pour ne pas mélanger
                la supervision cartographique avec le dashboard général ou le traitement détaillé des incidents.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('attacks.index') }}" class="btn btn-primary">Retour aux incidents</a>
                <a href="{{ route('attacks.live') }}" class="btn btn-secondary-outline">Vue live</a>
            </div>
        </div>

        <div class="dashboard-health dashboard-health--medium">
            <div class="dashboard-health-label">Vue cartographique</div>
            <div class="dashboard-health-value">Carte actualisée toutes les 10 secondes</div>
            <div class="dashboard-health-meta">
                Les lignes et points sont recalculés à partir des dernières attaques géolocalisées.
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong id="geo-total">0</strong>
                    <span>attaquants</span>
                </div>
                <div>
                    <strong id="attacker-count">0</strong>
                    <span>pays visibles</span>
                </div>
            </div>
        </div>
    </section>

    <section class="attacks-overview-grid">
        <article class="attacks-overview-card attacks-overview-card--neutral">
            <span class="attacks-overview-label">Géolocalisés</span>
            <strong id="geo-total-card">0</strong>
            <p>Sources visibles sur la carte actuelle.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--critical">
            <span class="attacks-overview-label">Critiques</span>
            <strong id="geo-critical">0</strong>
            <p>Points d’attaque de criticité maximale.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--medium">
            <span class="attacks-overview-label">Bloquées</span>
            <strong id="geo-blocked">0</strong>
            <p>IPs déjà neutralisées dans les dernières remontées.</p>
        </article>
        <article class="attacks-overview-card attacks-overview-card--high">
            <span class="attacks-overview-label">Actualisation</span>
            <strong>10s</strong>
            <p>Cycle automatique de rafraîchissement de la carte.</p>
        </article>
    </section>

    <div class="geo-layout-grid">
        <section class="card dashboard-panel geo-map-shell">
            <div class="section-header">
                <div>
                    <div class="section-title">Carte des attaques</div>
                    <p class="section-intro">Les points critiques sont mis en avant, avec leurs trajectoires vers la cible protégée.</p>
                </div>
                <div class="geo-toolbar">
                    <button class="btn btn-primary btn-sm" onclick="refreshGeo()">Actualiser</button>
                </div>
            </div>

            <div class="geo-map" id="world-map">
                <svg id="map-svg" viewBox="0 0 800 450" xmlns="http://www.w3.org/2000/svg">
                    <rect width="800" height="450" fill="#eff6ff"/>

                    <g stroke="rgba(37, 99, 235, 0.08)" stroke-width="0.5">
                        <line x1="0" y1="225" x2="800" y2="225"/>
                        <line x1="400" y1="0" x2="400" y2="450"/>
                        @for($i = 0; $i < 8; $i++)
                            <line x1="{{ $i * 100 }}" y1="0" x2="{{ $i * 100 }}" y2="450"/>
                        @endfor
                        @for($i = 0; $i < 5; $i++)
                            <line x1="0" y1="{{ $i * 90 }}" x2="800" y2="{{ $i * 90 }}"/>
                        @endfor
                    </g>

                    <path d="M80,80 L180,70 L200,90 L210,130 L190,160 L160,180 L120,200 L80,220 L60,200 L50,160 L60,120 Z" fill="rgba(37, 99, 235, 0.08)" stroke="rgba(37, 99, 235, 0.14)" stroke-width="1"/>
                    <path d="M140,220 L185,215 L200,240 L190,310 L170,350 L150,360 L135,330 L120,280 L125,240 Z" fill="rgba(37, 99, 235, 0.08)" stroke="rgba(37, 99, 235, 0.14)" stroke-width="1"/>
                    <path d="M340,70 L400,65 L420,80 L415,110 L390,125 L360,120 L340,105 L330,85 Z" fill="rgba(37, 99, 235, 0.08)" stroke="rgba(37, 99, 235, 0.14)" stroke-width="1"/>
                    <path d="M345,130 L400,125 L430,145 L440,200 L430,270 L400,310 L370,305 L345,270 L335,200 L335,150 Z" fill="rgba(37, 99, 235, 0.08)" stroke="rgba(37, 99, 235, 0.14)" stroke-width="1"/>
                    <path d="M430,55 L600,50 L650,80 L660,130 L620,155 L560,160 L500,145 L450,130 L425,105 L420,75 Z" fill="rgba(37, 99, 235, 0.08)" stroke="rgba(37, 99, 235, 0.14)" stroke-width="1"/>
                    <path d="M420,50 L600,45 L650,65 L640,85 L580,90 L500,85 L440,75 Z" fill="rgba(37, 99, 235, 0.06)" stroke="rgba(37, 99, 235, 0.12)" stroke-width="1"/>
                    <path d="M580,270 L650,265 L680,300 L670,340 L640,355 L600,345 L570,315 L565,285 Z" fill="rgba(37, 99, 235, 0.08)" stroke="rgba(37, 99, 235, 0.14)" stroke-width="1"/>

                    <g id="attack-lines"></g>
                    <g id="attack-points"></g>

                    <circle cx="390" cy="230" r="6" fill="var(--accent-green)" opacity="0.9"/>
                    <circle cx="390" cy="230" r="6" fill="none" stroke="var(--accent-green)" stroke-width="1.5" class="pulse-circle"/>
                    <circle cx="390" cy="230" r="6" fill="none" stroke="var(--accent-green)" stroke-width="1" opacity="0.4" class="pulse-circle" style="animation-delay:0.5s;"/>
                    <text x="398" y="226" font-family="IBM Plex Mono" font-size="10" fill="var(--accent-green)">CIBLE</text>
                </svg>

                <div class="geo-map-legend">
                    <div class="geo-legend-item"><span class="geo-legend-dot" style="background: var(--critical);"></span> Critique</div>
                    <div class="geo-legend-item"><span class="geo-legend-dot" style="background: var(--high);"></span> Élevée</div>
                    <div class="geo-legend-item"><span class="geo-legend-dot" style="background: var(--medium);"></span> Moyenne</div>
                    <div class="geo-legend-item"><span class="geo-legend-dot" style="background: var(--low);"></span> Faible</div>
                    <div class="geo-legend-item"><span class="geo-legend-dot" style="background: var(--accent-green);"></span> Cible</div>
                </div>
            </div>
        </section>

        <section class="card dashboard-panel geo-list-shell">
            <div class="section-header">
                <div>
                    <div class="section-title">Pays les plus actifs</div>
                    <p class="section-intro">Regroupement rapide par pays pour identifier les zones les plus présentes dans le flux.</p>
                </div>
            </div>

            <div class="geo-list" id="attacker-list">
                <div class="empty-state">
                    <div class="empty-state-icon">🌍</div>
                    <p class="empty-state-title">Chargement de la carte</p>
                    <p class="empty-state-text">Les pays actifs apparaîtront ici après le premier chargement des données.</p>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
function latLonToSvg(lat, lon) {
    const x = (lon + 180) * (800 / 360);
    const y = (90 - lat) * (450 / 180);
    return { x, y };
}

const TARGET = { x: 390, y: 230 };

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
        const response = await fetch('/api/geo-data');
        const data = await response.json();

        document.getElementById('geo-total').textContent = data.stats.total;
        document.getElementById('geo-total-card').textContent = data.stats.total;
        document.getElementById('geo-critical').textContent = data.stats.critical;
        document.getElementById('geo-blocked').textContent = data.stats.blocked;

        renderMapPoints(data.attacks);
        renderAttackerList(data.attacks);
    } catch (error) {
        console.error(error);
    }
}

function renderMapPoints(attacks) {
    const lines = document.getElementById('attack-lines');
    const points = document.getElementById('attack-points');

    lines.innerHTML = '';
    points.innerHTML = '';

    const colors = { critical: '#dc2626', high: '#ea580c', medium: '#ca8a04', low: '#16a34a' };

    attacks.slice(0, 60).forEach((attack, index) => {
        if (!attack.lat || !attack.lon) {
            return;
        }

        const source = latLonToSvg(attack.lat, attack.lon);
        const color = colors[attack.severity] || '#2563eb';
        const delay = (index * 0.1) % 3;
        const middleX = (source.x + TARGET.x) / 2 + (Math.random() - 0.5) * 80;
        const middleY = (source.y + TARGET.y) / 2 - Math.abs(source.x - TARGET.x) * 0.2;

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        line.setAttribute('d', `M${source.x},${source.y} Q${middleX},${middleY} ${TARGET.x},${TARGET.y}`);
        line.setAttribute('class', 'attack-line');
        line.setAttribute('stroke', color);
        line.setAttribute('stroke-width', attack.severity === 'critical' ? '2' : '1');
        line.setAttribute('opacity', attack.status === 'blocked' ? '0.15' : '0.4');
        line.setAttribute('stroke-dasharray', '8 4');
        line.style.animationDelay = `${delay}s`;
        lines.appendChild(line);

        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', source.x);
        circle.setAttribute('cy', source.y);
        circle.setAttribute('r', attack.severity === 'critical' ? '5' : '4');
        circle.setAttribute('fill', color);
        circle.setAttribute('opacity', '0.85');

        const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        title.textContent = `${attack.type} — ${attack.ip}\n${attack.city}, ${attack.country}\n${attack.severity}`;
        circle.appendChild(title);
        points.appendChild(circle);

        if (attack.severity === 'critical') {
            const pulse = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            pulse.setAttribute('cx', source.x);
            pulse.setAttribute('cy', source.y);
            pulse.setAttribute('r', '5');
            pulse.setAttribute('fill', 'none');
            pulse.setAttribute('stroke', color);
            pulse.setAttribute('stroke-width', '1');
            pulse.setAttribute('class', 'pulse-circle');
            pulse.style.animationDelay = `${delay + 0.3}s`;
            points.appendChild(pulse);
        }
    });
}

function renderAttackerList(attacks) {
    const list = document.getElementById('attacker-list');

    if (!attacks.length) {
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">🗺️</div>
                <p class="empty-state-title">Aucune donnée cartographique</p>
                <p class="empty-state-text">Les prochains attaquants géolocalisés apparaîtront ici.</p>
            </div>
        `;
        document.getElementById('attacker-count').textContent = '0';
        return;
    }

    const byCountry = {};

    attacks.forEach(attack => {
        if (!byCountry[attack.country]) {
            byCountry[attack.country] = { count: 0, severity: 'low', city: attack.city };
        }

        byCountry[attack.country].count++;

        const order = ['critical', 'high', 'medium', 'low'];
        if (order.indexOf(attack.severity) < order.indexOf(byCountry[attack.country].severity)) {
            byCountry[attack.country].severity = attack.severity;
        }
    });

    const sorted = Object.entries(byCountry).sort((a, b) => b[1].count - a[1].count).slice(0, 20);
    document.getElementById('attacker-count').textContent = sorted.length;

    list.innerHTML = sorted.map(([country, info]) => `
        <div class="geo-list-item">
            <span class="geo-list-flag">${getCountryFlag(country)}</span>
            <div class="geo-list-main">
                <div class="geo-list-country">${country}</div>
                <div class="geo-list-city">${info.city}</div>
            </div>
            <div class="geo-list-side">
                <div class="geo-list-count">${info.count}</div>
                <span class="badge badge-${info.severity}">${info.severity.toUpperCase()}</span>
            </div>
        </div>
    `).join('');
}

function refreshGeo() {
    loadGeoData();
}

loadGeoData();
window.setInterval(loadGeoData, 10000);
</script>
@endpush
