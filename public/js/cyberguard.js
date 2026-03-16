/**
 * CyberGuard — Moteur JavaScript Principal
 * Gère: Alarmes sonores, TTS, Toasts, Polling, CSRF
 */

/* ══════════════════════════════════════════
   AUDIO ENGINE — Alarmes + Voix
   ══════════════════════════════════════════ */
const AudioCtx = window.AudioContext || window.webkitAudioContext;
let audioCtx    = null;
let alarmActive = false;
let alarmInterval = null;

function initAudio() {
    if (!audioCtx) {
        try { audioCtx = new AudioCtx(); } catch(e) { console.warn('AudioContext unavailable'); }
    }
}

function playBeep(freq, duration, type = 'sawtooth', volume = 0.3) {
    if (!audioCtx) return;
    try {
        const osc  = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.type = type;
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        gain.gain.setValueAtTime(volume, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
    } catch(e) {}
}

function playSiren() {
    if (!audioCtx) return;
    // Sirène montante/descendante
    const osc   = audioCtx.createOscillator();
    const gain  = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    osc.type = 'sawtooth';
    gain.gain.setValueAtTime(0.35, audioCtx.currentTime);
    // Sweeper
    osc.frequency.setValueAtTime(440, audioCtx.currentTime);
    osc.frequency.linearRampToValueAtTime(880, audioCtx.currentTime + 0.5);
    osc.frequency.linearRampToValueAtTime(440, audioCtx.currentTime + 1.0);
    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 1.1);
    osc.start();
    osc.stop(audioCtx.currentTime + 1.1);
}

function speakAlert() {
    if (!('speechSynthesis' in window)) return;
    window.speechSynthesis.cancel();
    const phrases = ['ALERTE SYSTÈME', 'ALERTE SYSTÈME', 'ATTAQUE DÉTECTÉE', 'ALERTE SYSTÈME'];
    let i = 0;
    const speak = () => {
        if (i >= phrases.length || !alarmActive) return;
        const utt = new SpeechSynthesisUtterance(phrases[i]);
        utt.lang    = 'fr-FR';
        utt.rate    = 0.82;
        utt.pitch   = 0.65;
        utt.volume  = 1.0;
        utt.onend = () => { i++; setTimeout(speak, 280); };
        window.speechSynthesis.speak(utt);
    };
    speak();
}

function triggerAlarm(severity = 'high') {
    initAudio();
    if (alarmActive) return;
    alarmActive = true;

    // Afficher l'overlay visuel
    const overlay = document.getElementById('alarm-overlay');
    const banner  = document.getElementById('alarm-banner');
    if (overlay) overlay.style.display = 'block';
    if (banner)  banner.style.display  = 'block';

    // Flash rouge du body
    document.body.style.transition = 'background .15s';

    // Séquence sonore initiale
    const freq = severity === 'critical' ? 960 : 740;
    [0, 320, 640, 960].forEach((d, i) => {
        setTimeout(() => {
            if (!audioCtx) return;
            playBeep(freq + (i % 2) * 100, 0.22, 'sawtooth', 0.42);
        }, d);
    });
    // Sirène une fois
    setTimeout(playSiren, 1200);
    // Voix
    setTimeout(speakAlert, 600);

    // Bips répétitifs
    alarmInterval = setInterval(() => {
        if (!alarmActive) return;
        playBeep(freq, 0.12, 'square', 0.28);
        setTimeout(() => playBeep(freq * 1.18, 0.12, 'square', 0.28), 200);
    }, 2800);

    // Auto-stop 20s
    setTimeout(stopAlarm, 20000);
}

function stopAlarm() {
    alarmActive = false;
    clearInterval(alarmInterval);
    const overlay = document.getElementById('alarm-overlay');
    const banner  = document.getElementById('alarm-banner');
    if (overlay) overlay.style.display = 'none';
    if (banner)  banner.style.display  = 'none';
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
}

function triggerManualAlarm() {
    initAudio();
    if (alarmActive) {
        stopAlarm();
        showToast('🔕 Alarme', 'Alarme désactivée', 'low', 3000);
        return;
    }
    triggerAlarm('critical');
    showToast('🔊 Alarme', 'Test alarme déclenché', 'medium', 4000);
}

/* ══════════════════════════════════════════
   TOAST NOTIFICATIONS
   ══════════════════════════════════════════ */
const TOAST_ICONS = { critical: '💀', high: '🔴', medium: '⚠️', low: '✅', info: 'ℹ️' };

function showToast(title, message, severity = 'medium', autoClose = 6000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const t = document.createElement('div');
    t.className = `toast toast-${severity}`;
    t.innerHTML = `
        <div class="toast-icon">${TOAST_ICONS[severity] || '⚡'}</div>
        <div class="toast-body" style="flex:1;">
            <div class="toast-title">${title}</div>
            <div class="toast-msg">${message}</div>
        </div>
        <button onclick="this.parentElement.remove()"
            style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:18px;line-height:1;padding:0 0 0 8px;">×</button>
    `;
    container.appendChild(t);

    // Limit to 5 toasts
    while (container.children.length > 5) {
        container.removeChild(container.firstChild);
    }

    if (autoClose > 0) {
        setTimeout(() => {
            t.style.animation = 'toastOut .3s ease-in forwards';
            setTimeout(() => t.remove(), 300);
        }, autoClose);
    }
}

/* ══════════════════════════════════════════
   CLOCK
   ══════════════════════════════════════════ */
function updateClock() {
    const now = new Date();
    const clockEl = document.getElementById('clock');
    const dateEl  = document.getElementById('date-display');
    if (clockEl) clockEl.textContent = now.toLocaleTimeString('fr-FR', { hour12: false });
    if (dateEl)  dateEl.textContent  = now.toLocaleDateString('fr-FR', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' });
}
updateClock();
setInterval(updateClock, 1000);

/* ══════════════════════════════════════════
   CSRF FETCH HELPER
   ══════════════════════════════════════════ */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function csrfFetch(url, options = {}) {
    return fetch(url, {
        ...options,
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  csrfToken || '',
            'Accept':        'application/json',
            ...(options.headers || {}),
        },
    });
}

/* ══════════════════════════════════════════
   ALERT COUNT POLLING
   ══════════════════════════════════════════ */
let prevAlertCount = 0;

async function pollAlerts() {
    try {
        const res = await fetch('/api/alert-count');
        if (!res.ok) return;
        const d   = await res.json();
        const cnt = d.count || 0;

        // Update badge counters
        const topBadge = document.getElementById('topbar-alert-count');
        const navBadge = document.getElementById('nav-alert-count');
        if (topBadge) topBadge.textContent = cnt;
        if (navBadge) navBadge.textContent  = cnt;

        // Update threat level indicator
        const ind = document.getElementById('threat-indicator');
        const txt = document.getElementById('threat-text');
        if (ind && txt) {
            if (d.critical > 0) {
                ind.className = 'threat-level critical';
                txt.textContent = '⚠ MENACE CRITIQUE';
            } else if (cnt > 5) {
                ind.className = 'threat-level high';
                txt.textContent = 'MENACE ÉLEVÉE';
            } else if (cnt > 0) {
                ind.className = 'threat-level high';
                txt.textContent = `${cnt} ALERTE(S)`;
            } else {
                ind.className = 'threat-level normal';
                txt.textContent = 'NIVEAU NORMAL';
            }
        }

        // Trigger alarm on new critical alerts
        if (cnt > prevAlertCount && d.critical > 0 && !alarmActive) {
            triggerAlarm('critical');
        }
        prevAlertCount = cnt;
    } catch (e) { /* silently fail */ }
}

pollAlerts();
setInterval(pollAlerts, 6000);

/* ══════════════════════════════════════════
   TOAST CSS ANIMATIONS (injected)
   ══════════════════════════════════════════ */
const style = document.createElement('style');
style.textContent = `
@keyframes toastOut { from{opacity:1;transform:translateX(0)} to{opacity:0;transform:translateX(120px)} }
`;
document.head.appendChild(style);

/* ══════════════════════════════════════════
   GLOBAL UTILITIES
   ══════════════════════════════════════════ */
function formatNumber(n) {
    return Number(n).toLocaleString('fr-FR');
}

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)   return `Il y a ${diff}s`;
    if (diff < 3600) return `Il y a ${Math.floor(diff/60)}m`;
    if (diff < 86400)return `Il y a ${Math.floor(diff/3600)}h`;
    return `Il y a ${Math.floor(diff/86400)}j`;
}

function severityColor(s) {
    return { critical:'#ff0040', high:'#ff6b00', medium:'#ffd600', low:'#00ff88' }[s] || '#00e5ff';
}

// Expose globally
window.CyberGuard = {
    triggerAlarm, stopAlarm, triggerManualAlarm,
    showToast, csrfFetch,
    formatNumber, timeAgo, severityColor,
};
