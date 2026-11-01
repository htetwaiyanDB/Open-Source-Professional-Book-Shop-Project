(() => {
    if (window.__bookPalacePageTransitions) {
        return;
    }
    window.__bookPalacePageTransitions = true;

    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const nativePageTransitions = CSS.supports?.('view-transition-name: root') ?? false;
    let navigationStarted = false;

    const resetPage = () => {
        navigationStarted = false;
        root.classList.remove('is-page-leaving');
    };

    window.addEventListener('pageshow', resetPage);

    document.addEventListener('click', (event) => {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey ||
            reducedMotion.matches ||
            nativePageTransitions ||
            navigationStarted
        ) {
            return;
        }

        const link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('download') || link.dataset.noTransition !== undefined) {
            return;
        }

        const target = (link.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') {
            return;
        }

        const destination = new URL(link.href, window.location.href);
        const current = new URL(window.location.href);
        const sameDocumentHash =
            destination.origin === current.origin &&
            destination.pathname === current.pathname &&
            destination.search === current.search &&
            destination.hash;

        if (
            destination.origin !== current.origin ||
            !['http:', 'https:'].includes(destination.protocol) ||
            sameDocumentHash
        ) {
            return;
        }

        event.preventDefault();
        navigationStarted = true;
        root.classList.add('is-page-leaving');

        window.setTimeout(() => {
            window.location.assign(destination.href);
        }, 70);
    });
})();
