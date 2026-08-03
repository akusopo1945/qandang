import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    if (mobileMenuToggle && mobileMenu) {
        const closeMenu = () => {
            mobileMenu.hidden = true;
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
        };

        const openMenu = () => {
            mobileMenu.hidden = false;
            mobileMenuToggle.setAttribute('aria-expanded', 'true');
        };

        mobileMenuToggle.addEventListener('click', () => {
            if (mobileMenu.hidden) {
                openMenu();
                return;
            }

            closeMenu();
        });

        mobileMenu.querySelectorAll('[data-mobile-menu-close]').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });
    }

    document.querySelectorAll('[data-dismissible-banner]').forEach((banner) => {
        const dismissButton = banner.querySelector('[data-dismissible-banner-close]');

        if (!dismissButton) {
            return;
        }

        dismissButton.addEventListener('click', () => {
            banner.hidden = true;
        });
    });
});
