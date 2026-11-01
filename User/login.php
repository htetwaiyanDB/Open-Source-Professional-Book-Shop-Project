<?php
if (isset($_POST['btnuserlogin'])) {
    session_start();
    require_once 'userfunction.php';
    require_once '../Admin/connect.php';
    user_login();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css?v=20261101-performance">
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body class="auth-body">
    <main class="auth-shell auth-shell--login">
        <section class="auth-panel auth-panel--intro">
            <div class="auth-badge">Book Palace</div>
            <h1>Welcome back.</h1>
            <p>Sign in to continue shopping, manage your cart, and explore your favorite books with less friction.</p>

            <div class="auth-feature-list">
                <div class="auth-feature-item">
                    <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#book"></use></svg>
                    <span>Continue browsing books</span>
                </div>
                <div class="auth-feature-item">
                    <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#cart"></use></svg>
                    <span>Access your cart quickly</span>
                </div>
                <div class="auth-feature-item">
                    <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#shield"></use></svg>
                    <span>Simple and secure sign in</span>
                </div>
            </div>
        </section>

        <section class="auth-panel auth-panel--form">
            <div class="auth-topbar">
                <a href="home.php" class="auth-backlink"><svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#arrow-left"></use></svg> Back to Home</a>
            </div>

            <div class="auth-form-header">
                <h2>Login to your account</h2>
                <p>Enter your username and password to continue.</p>
            </div>

            <form action="" method="post" class="auth-form">
                <label class="auth-field">
                    <span>Username</span>
                    <div class="auth-input">
                        <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#user"></use></svg>
                        <input type="text" name="username" placeholder="Enter your username" required>
                    </div>
                </label>

                <label class="auth-field">
                    <span>Password</span>
                    <div class="auth-input">
                        <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#lock"></use></svg>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </label>

                <button type="submit" class="auth-submit" name="btnuserlogin">Login</button>

                <div class="auth-helper">
                    <span>Don't have an account?</span>
                    <a href="register.php">Create one</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
