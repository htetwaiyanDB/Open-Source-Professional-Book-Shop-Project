<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
require_once __DIR__ . '/userfunction.php';
if (isset($_POST['register'])) {
    $user_name = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmpassword'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $errors = array(
        'username' => '',
        'password' => '',
        'confirm_password' => '',
        'match_password' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
    );

    if (empty($user_name)) {
        $errors['username'] = 'Enter a username.';
    } elseif (strlen($user_name) < 3) {
        $errors['username'] = 'Username must be at least 3 characters.';
    }

    if (empty($password)) {
        $errors['password'] = 'Enter a password.';
    } elseif (strlen($password) < 12) {
        $errors['password'] = 'Password must be at least 12 characters.';
    }

    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Enter your password again.';
    } elseif ($password !== $confirm_password) {
        $errors['match_password'] = 'Passwords do not match.';
    }

    if (empty($email)) {
        $errors['email'] = 'Enter your email address.';
    }
    if (empty($phone)) {
        $errors['phone'] = 'Enter your phone number.';
    }
    if (empty($address)) {
        $errors['address'] = 'Enter your delivery address.';
    }

    foreach ($errors as $key => $value) {
        if ($value === '') {
            unset($errors[$key]);
        }
    }

    if (empty($errors)) {
        create_accu();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Book Palace</title>
    <link rel="stylesheet" href="login.css?v=20261102-simple">
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
    <script defer src="../assets/js/auth-form.js?v=20261102-simple"></script>
</head>
<body class="auth-body">
    <main class="auth-card auth-card--register">
        <a href="home.php" class="auth-backlink">
            <svg class="auth-icon" aria-hidden="true"><use href="auth-icons.svg#arrow-left"></use></svg>
            Back to home
        </a>

        <header class="auth-header">
            <img class="auth-logo" src="../Photos/logo4.png" alt="Book Palace">
            <h1>Create your account</h1>
            <p>Enter your details to start shopping.</p>
        </header>

        <form action="" method="post" class="auth-form auth-form--register">
            <?php echo csrf_field(); echo render_flash(); ?>

            <div class="auth-grid">
                <div class="auth-field">
                    <label for="register-username">Username</label>
                    <input class="auth-control" id="register-username" type="text" name="username" value="<?php echo isset($user_name) ? e($user_name) : ''; ?>" autocomplete="username" placeholder="Choose a username" minlength="3" maxlength="80" required>
                    <?php if (isset($errors['username'])): ?><small class="auth-error"><?php echo e($errors['username']); ?></small><?php endif; ?>
                </div>

                <div class="auth-field">
                    <label for="register-email">Email</label>
                    <input class="auth-control" id="register-email" type="email" name="email" value="<?php echo isset($email) ? e($email) : ''; ?>" autocomplete="email" placeholder="you@example.com" required>
                    <?php if (isset($errors['email'])): ?><small class="auth-error"><?php echo e($errors['email']); ?></small><?php endif; ?>
                </div>
            </div>

            <div class="auth-grid">
                <div class="auth-field">
                    <label for="register-password">Password</label>
                    <div class="auth-password">
                        <input class="auth-control" id="register-password" type="password" name="password" autocomplete="new-password" placeholder="At least 12 characters" minlength="12" maxlength="72" required>
                        <button class="auth-password-toggle" type="button" data-password-toggle="register-password" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                    <?php if (isset($errors['password'])): ?><small class="auth-error"><?php echo e($errors['password']); ?></small><?php endif; ?>
                </div>

                <div class="auth-field">
                    <label for="register-confirm-password">Confirm password</label>
                    <div class="auth-password">
                        <input class="auth-control" id="register-confirm-password" type="password" name="confirmpassword" autocomplete="new-password" placeholder="Enter it again" minlength="12" maxlength="72" required>
                        <button class="auth-password-toggle" type="button" data-password-toggle="register-confirm-password" aria-label="Show password" aria-pressed="false">Show</button>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?><small class="auth-error"><?php echo e($errors['confirm_password']); ?></small><?php endif; ?>
                    <?php if (isset($errors['match_password'])): ?><small class="auth-error"><?php echo e($errors['match_password']); ?></small><?php endif; ?>
                </div>
            </div>

            <div class="auth-grid">
                <div class="auth-field">
                    <label for="register-phone">Phone</label>
                    <input class="auth-control" id="register-phone" type="tel" name="phone" value="<?php echo isset($phone) ? e($phone) : ''; ?>" autocomplete="tel" placeholder="Your phone number" required>
                    <?php if (isset($errors['phone'])): ?><small class="auth-error"><?php echo e($errors['phone']); ?></small><?php endif; ?>
                </div>

                <div class="auth-field">
                    <label for="register-address">Delivery address</label>
                    <input class="auth-control" id="register-address" type="text" name="address" value="<?php echo isset($address) ? e($address) : ''; ?>" autocomplete="street-address" placeholder="Your current address" required>
                    <?php if (isset($errors['address'])): ?><small class="auth-error"><?php echo e($errors['address']); ?></small><?php endif; ?>
                </div>
            </div>

            <button type="submit" class="auth-submit" name="register">Create account</button>
        </form>

        <p class="auth-helper">Already have an account? <a href="login.php">Sign in</a></p>
    </main>
</body>
</html>
