// dashboard.js

(function () {
    'use strict';

    // Créer le conteneur de toasts s'il n'existe pas
    function createToastContainer() {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // Afficher un toast
    function showToast(message, type = 'success', duration = 4000, legacyDuration = null) {
        const toastContainer = createToastContainer();

        const knownTypes = ['success', 'error', 'warning', 'info', 'low', 'medium', 'high'];

        if (!knownTypes.includes(type) && typeof duration === 'string' && knownTypes.includes(duration)) {
            message = `${message} ${type}`.trim();
            type = duration;
            duration = typeof legacyDuration === 'number' ? legacyDuration : 4000;
        } else if (typeof type === 'number') {
            duration = type;
            type = 'success';
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        toast.innerHTML = `
            <div class="toast-content">${message}</div>
            <button class="toast-close" aria-label="Fermer">&times;</button>
        `;

        toastContainer.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease-out';
        }, 10);

        // Auto remove after custom duration
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);

        // Manual close on click
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        });
    }

    // Exposer les fonctions toast globalement
    window.toast = {
        show: showToast,
        success: (msg) => showToast(msg, 'success'),
        error: (msg) => showToast(msg, 'error'),
        warning: (msg) => showToast(msg, 'warning'),
        info: (msg) => showToast(msg, 'info')
    };
    window.showToast = showToast;
    window.csrfFetch = async (url, options = {}) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const headers = new Headers(options.headers || {});

        if (token) {
            headers.set('X-CSRF-TOKEN', token);
        }

        if (!headers.has('Accept')) {
            headers.set('Accept', 'application/json');
        }

        if (typeof options.body === 'string' && !headers.has('Content-Type')) {
            headers.set('Content-Type', 'application/json');
        }

        return fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers,
        });
    };

    const attackChartElement = document.getElementById('attackChart');
    if (attackChartElement && typeof window.Chart === 'function') {
        const attackData = JSON.parse(attackChartElement.dataset.attackData || '{"labels":[],"values":[]}');
        const ctx = attackChartElement.getContext('2d');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: attackData.labels || [],
                datasets: [{
                    data: attackData.values || [],
                    backgroundColor: [
                        '#ff0040', '#ff6b00', '#ffd600', '#00ff88',
                        '#00e5ff', '#a855f7', '#ec4899', '#3b82f6'
                    ],
                    borderColor: '#0a1520',
                    borderWidth: 2
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
    }

    const totalStat = document.getElementById('stat-total');
    if (!totalStat) {
        return;
    }

    function updateText(id, value, formatter = null) {
        const element = document.getElementById(id);

        if (!element) {
            return;
        }

        element.textContent = formatter ? formatter(value) : value;
    }

    let prevTotal = Number.parseInt(totalStat.textContent, 10) || 0;

    setInterval(async () => {
        try {
            const res = await fetch('/api/stats');
            const data = await res.json();

            updateText('stat-total', data.total_attacks);
            updateText('stat-critical', data.critical);
            updateText('stat-blocked', data.blocked);
            updateText('stat-active', data.active);
            updateText('stat-unread-alerts', data.unread_alerts);
            updateText('stat-auth-audit', data.auth_audit_events);
            updateText('stat-intranet-audit', data.intranet_audit_events);
            updateText('stat-manual-sims', data.manual_simulation_attacks);
            updateText('stat-countries', data.countries_count);
            updateText('stat-perhour', data.attacks_per_hour);
            updateText('stat-blocked-ips', data.blocked_ips_count);
            updateText('stat-block-rate', data.block_rate_percent, (value) => `${Number(value).toFixed(1)}%`);
            updateText('stat-mttr', data.mean_resolution_minutes, (value) => Number(value).toFixed(1));
            updateText('stat-top-type', data.top_attack_type);
            updateText('stat-high-risk', data.high_risk_ips);

            if (data.total_attacks > prevTotal) {
                prevTotal = data.total_attacks;

                if (data.critical > 0) {
                    showToast('Nouvelle attaque critique détectée!', 'error');
                }
            }
        } catch (e) {
            console.error(e);
        }
    }, 8000);

})();
