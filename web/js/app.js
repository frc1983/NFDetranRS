(function () {
    'use strict';

    const sidebar = document.querySelector('.app-sidebar');
    const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
    const toggleSidebar = (open) => {
        if (!sidebar || !sidebarBackdrop) return;
        sidebar.classList.toggle('open', open);
        sidebarBackdrop.classList.toggle('open', open);
        document.body.classList.toggle('overflow-hidden', open);
        document.querySelector('[data-sidebar-toggle]')?.setAttribute('aria-expanded', String(open));
    };

    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => toggleSidebar(true));
    document.querySelectorAll('[data-sidebar-close]').forEach((element) => element.addEventListener('click', () => toggleSidebar(false)));

    const drawer = document.querySelector('[data-audit-drawer]');
    const drawerBackdrop = document.querySelector('[data-drawer-backdrop]');
    const toggleDrawer = (open) => {
        if (!drawer || !drawerBackdrop) return;
        drawer.classList.toggle('open', open);
        drawerBackdrop.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', String(!open));
    };

    const openAuditDetail = (row) => {
        drawer?.querySelector('[data-field="event"]')?.replaceChildren(document.createTextNode(row.dataset.event || 'Evento'));
        drawer?.querySelector('[data-field="user"]')?.replaceChildren(document.createTextNode(row.dataset.user || 'Sistema'));
        drawer?.querySelector('[data-field="date"]')?.replaceChildren(document.createTextNode(row.dataset.date || '—'));
        toggleDrawer(true);
    };
    document.querySelectorAll('[data-audit-detail]').forEach((row) => {
        row.addEventListener('click', () => openAuditDetail(row));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openAuditDetail(row);
            }
        });
    });
    document.querySelectorAll('[data-drawer-close]').forEach((element) => element.addEventListener('click', () => toggleDrawer(false)));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            toggleSidebar(false);
            toggleDrawer(false);
        }
    });
})();
