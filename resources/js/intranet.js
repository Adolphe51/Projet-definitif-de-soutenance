/**
 * Système de thème (clair/sombre)
 */
class ThemeManager {
    constructor() {
        this.THEME_KEY = 'intranet-theme';
        this.init();
    }

    init() {
        const saved = localStorage.getItem(this.THEME_KEY);
        const isDark = saved === 'dark' ||
            (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (isDark) {
            this.enable();
        }

        // Écouteur pour les changements de préférence système
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (e.matches) this.enable();
            else this.disable();
        });
    }

    enable() {
        document.body.classList.add('dark');
        localStorage.setItem(this.THEME_KEY, 'dark');
    }

    disable() {
        document.body.classList.remove('dark');
        localStorage.setItem(this.THEME_KEY, 'light');
    }

    toggle() {
        if (document.body.classList.contains('dark')) {
            this.disable();
        } else {
            this.enable();
        }
    }
}

/**
 * Recherche en temps réel dans les tableaux
 */
class TableSearch {
    constructor(tableSelector = 'table') {
        this.table = document.querySelector(tableSelector);
        if (!this.table) return;

        this.setupSearchInput();
    }

    setupSearchInput() {
        const container = this.table.closest('.container') || document.body;
        const existingSearch = container.querySelector('.table-search-input');

        if (!existingSearch) {
            const searchDiv = document.createElement('div');
            searchDiv.className = 'form-group';
            searchDiv.innerHTML = `
                <label for="table-search">Rechercher</label>
                <input 
                    type="text" 
                    id="table-search" 
                    class="table-search-input" 
                    placeholder="Tapez pour filtrer les résultats..."
                >
            `;

            const firstChild = this.table.previousElementSibling || this.table;
            firstChild.parentNode.insertBefore(searchDiv, firstChild);
        }

        const searchInput = container.querySelector('.table-search-input');
        searchInput.addEventListener('input', (e) => this.filter(e.target.value));
    }

    filter(query) {
        const rows = this.table.querySelectorAll('tbody tr');
        const lowerQuery = query.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(lowerQuery) ? '' : 'none';
        });
    }
}

/**
 * Pagination côté client
 */
class ClientPagination {
    constructor(tableSelector = 'table', itemsPerPage = 10) {
        this.table = document.querySelector(tableSelector);
        if (!this.table) return;

        this.itemsPerPage = itemsPerPage;
        this.currentPage = 1;
        this.allRows = Array.from(this.table.querySelectorAll('tbody tr'));
        this.totalPages = Math.ceil(this.allRows.length / itemsPerPage);

        this.setupPagination();
    }

    setupPagination() {
        if (this.totalPages <= 1) return;

        const container = this.table.closest('.container') || this.table.parentNode;
        let paginationDiv = container.querySelector('.pagination-controls');

        if (!paginationDiv) {
            paginationDiv = document.createElement('div');
            paginationDiv.className = 'pagination-controls';
            paginationDiv.style.display = 'flex';
            paginationDiv.style.gap = '0.5rem';
            paginationDiv.style.marginTop = '1rem';
            paginationDiv.style.justifyContent = 'center';
            paginationDiv.style.alignItems = 'center';

            this.table.parentNode.appendChild(paginationDiv);
        }

        paginationDiv.innerHTML = '';

        // Bouton précédent
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '← Précédent';
        prevBtn.className = 'button secondary';
        prevBtn.disabled = this.currentPage === 1;
        prevBtn.addEventListener('click', () => this.previousPage());
        paginationDiv.appendChild(prevBtn);

        // Affichage page/total
        const pageInfo = document.createElement('span');
        pageInfo.textContent = `Page ${this.currentPage} / ${this.totalPages}`;
        pageInfo.style.marginLeft = '1rem';
        pageInfo.style.marginRight = '1rem';
        paginationDiv.appendChild(pageInfo);

        // Bouton suivant
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Suivant →';
        nextBtn.className = 'button secondary';
        nextBtn.disabled = this.currentPage === this.totalPages;
        nextBtn.addEventListener('click', () => this.nextPage());
        paginationDiv.appendChild(nextBtn);

        this.displayPage();
    }

    displayPage() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;

        this.allRows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });

        // Rafraîchir les boutons
        this.setupPagination();
    }

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.displayPage();
        }
    }

    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.displayPage();
        }
    }
}

/**
 * Modal de confirmation personnalisée
 */
class ConfirmationModal {
    constructor() {
        this.setupModal();
    }

    setupModal() {
        // Créer le modal HTML
        const modal = document.createElement('div');
        modal.id = 'confirmModal';
        modal.className = 'confirm-modal';
        modal.innerHTML = `
            <div class="confirm-modal-content">
                <h3 id="confirmTitle">Confirmation</h3>
                <p id="confirmMessage"></p>
                <div class="confirm-modal-actions">
                    <button class="button secondary" id="confirmCancel">Annuler</button>
                    <button class="button primary" id="confirmOk">Confirmer</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Ajouter les styles du modal
        const style = document.createElement('style');
        style.textContent = `
            .confirm-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                justify-content: center;
                align-items: center;
            }

            .confirm-modal.active {
                display: flex;
                animation: fadeIn 0.2s ease;
            }

            .confirm-modal-content {
                background: var(--color-bg-primary);
                border-radius: 1rem;
                padding: 2rem;
                max-width: 400px;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.3);
                animation: slideUp 0.3s ease;
            }

            .confirm-modal-content h3 {
                margin-top: 0;
                color: var(--color-text-primary);
            }

            .confirm-modal-content p {
                color: var(--color-text-secondary);
                margin: 1rem 0 1.5rem 0;
            }

            .confirm-modal-actions {
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }
        `;
        document.head.appendChild(style);

        // Événements
        document.getElementById('confirmCancel').addEventListener('click', () => {
            this.reject();
        });

        document.getElementById('confirmOk').addEventListener('click', () => {
            this.resolve();
        });

        // Fermer au clic sur le fond
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.reject();
            }
        });
    }

    show(title = 'Confirmation', message = 'Êtes-vous sûr ?') {
        return new Promise((resolve, reject) => {
            this.resolve = () => {
                this.hide();
                resolve(true);
            };
            this.reject = () => {
                this.hide();
                reject(false);
            };

            document.getElementById('confirmTitle').textContent = title;
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmModal').classList.add('active');
        });
    }

    hide() {
        document.getElementById('confirmModal').classList.remove('active');
    }
}

/**
 * Lazy loading pour les images
 */
class LazyLoadManager {
    constructor() {
        this.images = document.querySelectorAll('img[data-src]');
        this.init();
    }

    init() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            });

            this.images.forEach(img => observer.observe(img));
        } else {
            // Fallback pour les anciens navigateurs
            this.images.forEach(img => this.loadImage(img));
        }
    }

    loadImage(img) {
        const src = img.getAttribute('data-src');
        if (src) {
            img.src = src;
            img.removeAttribute('data-src');
        }
    }
}

/**
 * Confirmation d'actions avec modal personnalisée
 */
function setupConfirmationActions(modal) {
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', async (event) => {
            event.preventDefault();
            const message = element.getAttribute('data-confirm');

            try {
                await modal.show('Confirmation', message);
                // Si on arrive ici, l'utilisateur a cliqué OK
                if (element.tagName === 'FORM') {
                    element.submit();
                } else if (element.tagName === 'A') {
                    window.location.href = element.href;
                } else if (element.tagName === 'BUTTON') {
                    element.form?.submit();
                }
            } catch {
                // L'utilisateur a annulé, ne rien faire
            }
        });
    });
}

/**
 * Initialisation globale
 */
document.addEventListener('DOMContentLoaded', () => {
    // Thème
    const themeManager = new ThemeManager();

    // Ajouter un bouton de bascule du thème
    const navButtons = document.querySelector('.intranet-nav-inner');
    if (navButtons) {
        const themeToggle = document.createElement('button');
        themeToggle.id = 'theme-toggle';
        themeToggle.className = 'button secondary';
        themeToggle.textContent = '🌙';
        themeToggle.title = 'Basculer le thème';
        themeToggle.style.marginLeft = 'auto';
        themeToggle.addEventListener('click', () => {
            themeManager.toggle();
            themeToggle.textContent = document.body.classList.contains('dark') ? '☀️' : '🌙';
        });
        navButtons.appendChild(themeToggle);
    }

    // recherche dans les tabl eaux
    const tables = document.querySelectorAll('table');
    tables.forEach(() => new TableSearch('table'));

    // Pagination côté client (optionnel - décommenter si souhaité)
    // tables.forEach(() => new ClientPagination('table', 10));

    // Modal de confirmation
    const confirmModal = new ConfirmationModal();
    setupConfirmationActions(confirmModal);

    // Lazy loading
    new LazyLoadManager();

    // Navigation active
    const currentUrl = window.location.href;
    document.querySelectorAll('.intranet-nav-inner a').forEach(link => {
        if (link.href === currentUrl) {
            link.classList.add('active');
        }
    });
});

