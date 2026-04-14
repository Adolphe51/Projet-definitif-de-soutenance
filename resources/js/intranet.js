document.addEventListener('DOMContentLoaded', () => {
    const confirmElements = document.querySelectorAll('[data-confirm]');
    confirmElements.forEach((element) => {
        element.addEventListener('click', (event) => {
            const message = element.getAttribute('data-confirm');
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const intranetLinks = document.querySelectorAll('.intranet-nav-inner a');
    intranetLinks.forEach((link) => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
});
