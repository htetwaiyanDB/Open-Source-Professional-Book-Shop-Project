document.documentElement.classList.add('service-cards-ready');

document.addEventListener('DOMContentLoaded', () => {
    const cards = Array.from(document.querySelectorAll('.service-card'));
    const touchLayout = window.matchMedia('(max-width: 767px), (hover: none), (pointer: coarse)');

    const closeCard = (card) => {
        card.classList.remove('is-open');
        card.setAttribute('aria-expanded', 'false');
    };

    const toggleCard = (card) => {
        if (!touchLayout.matches) return;

        const shouldOpen = !card.classList.contains('is-open');
        cards.forEach(closeCard);

        if (shouldOpen) {
            card.classList.add('is-open');
            card.setAttribute('aria-expanded', 'true');
        }
    };

    const syncInteractionMode = () => {
        cards.forEach((card) => {
            closeCard(card);

            if (touchLayout.matches) {
                const title = card.querySelector('.card__title')?.textContent?.trim() || 'Service details';
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.setAttribute('aria-label', `${title} — tap to view details`);
            } else {
                card.removeAttribute('role');
                card.removeAttribute('tabindex');
                card.removeAttribute('aria-expanded');
                card.removeAttribute('aria-label');
            }
        });
    };

    cards.forEach((card) => {
        card.addEventListener('click', () => toggleCard(card));

        card.addEventListener('keydown', (event) => {
            if (!touchLayout.matches || (event.key !== 'Enter' && event.key !== ' ')) return;

            event.preventDefault();
            toggleCard(card);
        });
    });

    document.addEventListener('click', (event) => {
        if (!touchLayout.matches || cards.some((card) => card.contains(event.target))) return;
        cards.forEach(closeCard);
    });

    touchLayout.addEventListener?.('change', syncInteractionMode);
    syncInteractionMode();
});
