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
    function showToast(message, type = 'success', duration = 4000) {
        const toastContainer = createToastContainer();
        
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

// Données initiales pour le graphique
const attackData = JSON.parse(document.getElementById('attackChart').dataset.attackData || '{}');
const ctx = document.getElementById('attackChart').getContext('2d');

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(attackData),
        datasets: [{
            data: Object.values(attackData),
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

// Auto-refresh stats
let prevTotal = Number.parseInt(document.getElementById('stat-total').textContent);
setInterval(async () => {
    try {
        const res = await fetch('/api/stats');
        const data = await res.json();

        document.getElementById('stat-total').textContent = data.total_attacks;
        document.getElementById('stat-critical').textContent = data.critical;
        document.getElementById('stat-blocked').textContent = data.blocked;
        document.getElementById('stat-active').textContent = data.active;
        document.getElementById('stat-countries').textContent = data.countries_count;
        document.getElementById('stat-perhour').textContent = data.attacks_per_hour;
        document.getElementById('stat-blocked-ips').textContent = data.blocked_ips_count;
        document.getElementById('stat-active-honeypots').textContent = data.active_honeypots;

        if (data.total_attacks > prevTotal) {
            prevTotal = data.total_attacks;
            if (data.critical > 0) {
                showToast('💀 ATTAQUE CRITIQUE!', 'Nouvelle attaque critique détectée!', 'critical');
            }
        }
    } catch (e) { console.error(e); }
}, 8000);

})();
