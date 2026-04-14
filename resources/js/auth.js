/**
 * CyberGuard Authentication JavaScript
 * Professional interactions for authentication system
 */

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

    // =========================
    // DOM Elements
    // =========================
    const digits = document.querySelectorAll('.otp-input');
    const hiddenInput = document.getElementById('otpCode');
    const otpTimerElem = document.getElementById('otpTimer');
    const resendBtn = document.getElementById('resendBtn');
    const resendTimerElem = document.getElementById('resendTimer');
    const authForm = document.querySelector('.auth-form');
    const formInputs = document.querySelectorAll('.form-input');
    const authButton = document.querySelector('.auth-button');

    // =========================
    // OTP Input Management
    // =========================
    function updateOtpCode() {
        if (hiddenInput) {
            hiddenInput.value = [...digits].map(d => d.value).join('');
        }
    }

    function validateOtpInput(input, index) {
        // Only allow numbers
        input.value = input.value.replace(/[^0-9]/g, '');
        
        if (input.value.length === 1 && index < digits.length - 1) {
            digits[index + 1].focus();
        }
        
        updateOtpCode();
        updateOtpVisuals();
    }

    function updateOtpVisuals() {
        const filledCount = [...digits].filter(d => d.value).length;
        
        digits.forEach((input, index) => {
            input.classList.remove('filled', 'error');
            
            if (input.value) {
                input.classList.add('filled');
            }
        });
        
        // Auto-submit when all digits are filled
        if (filledCount === digits.length) {
            const form = document.querySelector('.otp-form');
            if (form) {
                form.submit();
            }
        }
    }

    if (digits.length) {
        digits.forEach((input, index) => {
            input.addEventListener('input', () => validateOtpInput(input, index));
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    digits[index - 1].focus();
                    digits[index - 1].select();
                } else if (e.key >= '0' && e.key <= '9') {
                    input.value = '';
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                const numbers = pastedData.replace(/[^0-9]/g, '').slice(0, digits.length);
                
                [...digits].forEach((digit, index) => {
                    digit.value = numbers[index] || '';
                });
                
                updateOtpCode();
                updateOtpVisuals();
            });
        });
    }

    // =========================
    // OTP Timer
    // =========================
    let otpSeconds = 120;
    let otpInterval;

    function startOtpTimer() {
        if (!otpTimerElem) return;

        otpInterval = setInterval(() => {
            const m = Math.floor(otpSeconds / 60);
            const s = otpSeconds % 60;

            otpTimerElem.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            otpTimerElem.style.color = otpSeconds < 30 ? '#ef4444' : '#94a3b8';

            otpSeconds--;

            if (otpSeconds < 0) {
                clearInterval(otpInterval);
                otpTimerElem.textContent = 'Expiré';
                otpTimerElem.style.color = '#ef4444';
                showOtpError('Le code OTP a expiré. Veuillez demander un nouveau code.');
            }
        }, 1000);
    }

    // =========================
    // Resend Timer
    // =========================
    let resendSeconds;
    let resendInterval;

    function startResendTimer() {
        if (!resendBtn || !resendTimerElem) return;

        // Réinitialiser le timer à 180 secondes
        resendSeconds = 180;

        resendBtn.disabled = true;
        resendBtn.style.opacity = '0.6';
        resendBtn.style.cursor = 'not-allowed';

        resendInterval = setInterval(() => {
            resendSeconds--;
            resendTimerElem.textContent = resendSeconds;

            if (resendSeconds <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
                resendBtn.style.cursor = 'pointer';
                resendBtn.innerHTML = '<span>Renvoyer le code</span>';
            } else {
                resendBtn.innerHTML = `<span>Recevoir un nouveau code dans <span style="color: var(--primary)">${resendSeconds}s</span></span>`;
            }
        }, 1000);
    }

    if (resendBtn) {
        resendBtn.addEventListener('click', (e) => {
            if (resendBtn.disabled) {
                e.preventDefault();
                showToast('Veuillez attendre la fin du minuteur', 'warning');
            }
        });
    }

    // =========================
    // Form Submission
    // =========================
    function handleFormSubmit(event) {
        if (!authButton) return;

        const form = event.target;
        let isValid = true;

        // Validate all inputs
        formInputs.forEach(input => {
            if (!validateFormInput(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault();
            showToast('Veuillez vérifier les champs du formulaire', 'error');
            return;
        }

        // Show loading state
        authButton.classList.add('loading');
        authButton.disabled = true;
        
        const originalText = authButton.innerHTML;
        authButton.innerHTML = '<div class="spinner"></div><span>Authentification...</span>';

        // Reset button after 3 seconds if not redirected
        setTimeout(() => {
            if (authButton.disabled) {
                authButton.classList.remove('loading');
                authButton.disabled = false;
                authButton.innerHTML = originalText;
                showToast('Une erreur est survenue, veuillez réessayer', 'error');
            }
        }, 3000);
    }

    if (authForm) {
        authForm.addEventListener('submit', handleFormSubmit);
    }

    // =========================
    // OTP Error Handling
    // =========================
    function showOtpError(message) {
        showToast(message, 'error', 5000);
    }

    // =========================
    // Toast Notifications
    // =========================
    // Les fonctions toast sont maintenant fournies par toast.js

    // =========================
    // Security Features
    // =========================
    
    // Prevent copy/paste on password fields
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('copy', (e) => e.preventDefault());
        input.addEventListener('cut', (e) => e.preventDefault());
        input.addEventListener('paste', (e) => e.preventDefault());
    });

    // Auto-logout warning
    let inactivityTimer;
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            showToast('Session inactive détectée. Veuillez rafraîchir la page.', 'warning');
        }, 300000); // 5 minutes
    }

    document.addEventListener('mousemove', resetInactivityTimer);
    document.addEventListener('keypress', resetInactivityTimer);
    document.addEventListener('click', resetInactivityTimer);

    // =========================
    // Accessibility
    // =========================
    function enhanceAccessibility() {
        // Add ARIA labels
        formInputs.forEach(input => {
            if (!input.getAttribute('aria-label')) {
                const label = input.previousElementSibling;
                if (label && label.tagName === 'LABEL') {
                    input.setAttribute('aria-label', label.textContent);
                }
            }
        });

        // Keyboard navigation for OTP
        digits.forEach((input, index) => {
            input.setAttribute('aria-label', `Chiffre ${index + 1} du code OTP`);
            input.setAttribute('inputmode', 'numeric');
            input.setAttribute('maxlength', '1');
        });
    }

    // =========================
    // Initialize
    // =========================
    function init() {
        enhanceAccessibility();
        startOtpTimer();
        startResendTimer();
        resetInactivityTimer();
        
        // Expose global functions
        window.CyberGuardAuth = {
            showToast,
            showOtpError,
            validateFormInput
        };

        // Also expose for session messages
        window.auth = {
            toast: {
                show: showToast,
                success: (msg) => showToast(msg, 'success'),
                error: (msg) => showToast(msg, 'error'),
                warning: (msg) => showToast(msg, 'warning'),
                info: (msg) => showToast(msg, 'info')
            }
        };

        // Expose toast methods globally for layouts
        window.toast = window.auth.toast;
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();