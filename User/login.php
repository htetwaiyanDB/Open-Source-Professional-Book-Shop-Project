<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_once __DIR__ . '/userfunction.php';
if (isset($_POST['btnuserlogin'])) {
    user_login();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Book Palace</title>
    <link rel="stylesheet" href="login.css?v=20261102-simple">
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
    <script defer src="../assets/js/auth-form.js?v=20261102-simple"></script>
</head>
<body class="auth-body">
    <main class="auth-card auth-card--login">
        <a href="home.php" class="auth-backlink">
            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#arrow-left"></use></svg>
            Back to home
        </a>

        <header class="auth-header">
            <img class="auth-logo" src="../Photos/logo4.png" alt="Book Palace">
            <h1>Welcome back</h1>
            <p>Sign in to your Book Palace account.</p>
        </header>

        <form action="" method="post" class="auth-form">
            <?php echo csrf_field(); echo render_flash(); ?>

            <div class="auth-field">
                <label for="login-username">Username</label>
                <input class="auth-control" id="login-username" type="text" name="username" autocomplete="username" placeholder="Enter your username" required autofocus>
            </div>

            <div class="auth-field">
                <label for="login-password">Password</label>
                <div class="auth-password">
                    <input class="auth-control" id="login-password" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                    <button class="auth-password-toggle" type="button" data-password-toggle="login-password" aria-label="Show password" aria-pressed="false">Show</button>
                </div>
            </div>

            <button type="submit" class="auth-submit" name="btnuserlogin">Sign in</button>
        </form>

        <p class="auth-helper">New to Book Palace? <a href="register.php">Create an account</a></p>
    </main>
</body>
</html>
