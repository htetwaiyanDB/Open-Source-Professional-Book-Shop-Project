document.querySelectorAll('[data-admin-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(button.getAttribute('data-admin-password-toggle'));
        var icon = button.querySelector('use');
        if (!input || !icon) return;

        var willShow = input.type === 'password';
        input.type = willShow ? 'text' : 'password';
        icon.setAttribute('href', willShow ? '../User/auth-icons.svg#eye-off' : '../User/auth-icons.svg#eye');
        button.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', willShow ? 'true' : 'false');
    });
});
