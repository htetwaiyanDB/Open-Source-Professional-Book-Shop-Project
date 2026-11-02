document.addEventListener('DOMContentLoaded', () => {
    const navigation = document.getElementById('navbarSupportedContent');
    if (!navigation || typeof bootstrap === 'undefined') return;

    navigation.querySelectorAll('a:not(.dropdown-toggle)').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 991.98px)').matches) {
                bootstrap.Collapse.getOrCreateInstance(navigation).hide();
            }
        });
    });
});
