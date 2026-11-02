document.documentElement.classList.add('service-cards-ready');

document.addEventListener('DOMContentLoaded', () => {
    const cards = Array.from(document.querySelectorAll('.service-card'));

    const closeCard = (card) => {
        const button = card.querySelector('.service-card__toggle');
        const label = button?.querySelector('span');

        card.classList.remove('is-open');
        if (!button) return;

        button.setAttribute('aria-expanded', 'false');
        if (label) label.textContent = button.dataset.openLabel || 'View details';
    };

    cards.forEach((card) => {
        const button = card.querySelector('.service-card__toggle');
        if (!button) return;

        button.addEventListener('click', () => {
            const shouldOpen = !card.classList.contains('is-open');

            cards.forEach(closeCard);

            if (shouldOpen) {
                const label = button.querySelector('span');
                card.classList.add('is-open');
                button.setAttribute('aria-expanded', 'true');
                if (label) label.textContent = button.dataset.closeLabel || 'Hide details';
            }
        });
    });
});
