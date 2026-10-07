<script>
(() => {
    const body = document.body;
    const storageKey = 'woven.sidebar.collapsed';
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const collapseBtn = document.querySelector('[data-sidebar-collapse]');
    const openBtns = document.querySelectorAll('[data-sidebar-open]');
    const closeBtns = document.querySelectorAll('[data-sidebar-close]');

    const isMobile = () => window.matchMedia('(max-width: 960px)').matches;

    const applyCollapsed = (collapsed) => {
        body.classList.toggle('sidebar-collapsed', collapsed && !isMobile());
        if (collapseBtn) {
            collapseBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            collapseBtn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            collapseBtn.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
        }
    };

    const openMobile = () => {
        body.classList.add('sidebar-open');
        body.classList.remove('sidebar-collapsed');
    };

    const closeMobile = () => body.classList.remove('sidebar-open');

    try {
        applyCollapsed(localStorage.getItem(storageKey) === '1');
    } catch (e) {
        applyCollapsed(false);
    }

    collapseBtn?.addEventListener('click', () => {
        if (isMobile()) {
            closeMobile();
            return;
        }
        const next = !body.classList.contains('sidebar-collapsed');
        applyCollapsed(next);
        try { localStorage.setItem(storageKey, next ? '1' : '0'); } catch (e) {}
    });

    openBtns.forEach((btn) => btn.addEventListener('click', openMobile));
    closeBtns.forEach((btn) => btn.addEventListener('click', closeMobile));
    overlay?.addEventListener('click', closeMobile);

    window.addEventListener('resize', () => {
        if (!isMobile()) {
            closeMobile();
            try {
                applyCollapsed(localStorage.getItem(storageKey) === '1');
            } catch (e) {}
        } else {
            body.classList.remove('sidebar-collapsed');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMobile();
    });
})();
</script>
